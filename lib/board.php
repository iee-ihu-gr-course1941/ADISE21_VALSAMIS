<?php

/**
 * prints board
 * @param json $input 
 * prints board using json format 
 */

function show_board($input)
{
    header('Content-type: application/json');
    print json_encode(read_board(), JSON_PRETTY_PRINT);
}

/**
 * reads board table
 * @return array with table contents
 *  table data (x , y , piece)
 */

function read_board()
{
    global $mysqli;
    $sql = 'select * from board';
    $st = $mysqli->prepare($sql);
    $st->execute();
    $res = $st->get_result();
    return ($res->fetch_all(MYSQLI_ASSOC));
}

/**
 * resets board 
 * calls mysql store procedures  
 * to reset all board rows 
 * to reset players rows
 * to reset piece availability
 * to reset game_status information
 */

function reset_board()
{
    global $mysqli;
    $sql = 'call clean_board()';
    $mysqli->query($sql);
    $sql1 = 'call reset_players()';
    $mysqli->query($sql1);
    $sql2 = 'call reset_pieces()';
    $mysqli->query($sql2);
    $sql3 = 'call clean_game_status()';
    $mysqli->query($sql3);
}

/**
 * prints the available pieces
 * availability can be true or false
 * @return json  all piece_id 
 */

function piece_list()
{
    global $mysqli;
    $sql = 'SELECT piece_id from pieces where available=true';
    $st = $mysqli->prepare($sql);
    $st->execute();
    $res = $st->get_result();
    print json_encode($res->fetch_all(), JSON_PRETTY_PRINT);
}

/**
 * finds and returns player id using token
 * @param $token 
 * @return int player id
 */


function get_player_id($token)
{
    global $mysqli;
    $sql = 'SELECT player_id from players where token=?';
    $st = $mysqli->prepare($sql);
    $st->bind_param('s', $token);
    $st->execute();
    $res = $st->get_result();
    $id = $res->fetch_assoc();
    return $id['player_id'];
}

/**
 * checks if player meets all requirements to pick piece
 * @param $input json  
 * calls do_pick_piece
 */

function pick_piece($input)
{
    if ($input['piece_id'] == "") {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "piece is not set."]);
        exit;
    }
    if ($input['token'] == null) {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "token is not set."]);
        exit;
    }
    $status = read_status();
    if ($status['status'] != 'started') {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "Game is not in action."]);
        exit;
    }
    if ($status['p_turn'] != get_player_id($input['token'])) {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "It is not your turn."]);
        exit;
    }
    do_pick_piece($input);
}

/**
 * picks the piece for other player to place
 * @param int $piece_id contains the id of the piece
 * id is [1-16] 
 * calls make_piece_unavailable
 * calls set_current_piece
 */

function do_pick_piece($input)
{
    make_piece_unavailable($input['piece_id']);
    set_current_piece($input['piece_id']);
    change_role_place($input['token']);
    next_player($input['token']);
}


/**
 * flags chosen piece unavailable
 * @param int $piece_id contains the id of the piece
 */

function make_piece_unavailable($piece_id)
{
    global $mysqli;
    $sql = 'UPDATE pieces SET available=false WHERE piece_id=?';
    $st = $mysqli->prepare($sql);
    $st->bind_param('i', $piece_id);
    $st->execute();
}

/**
 * stores in game status table the piece about to be played for the next player
 * @param int $piece_id contains the id of the piece
 */

function set_current_piece($piece_id)
{
    global $mysqli;
    $sql = 'UPDATE game_status  SET current_piece=? ';
    $st = $mysqli->prepare($sql);
    $st->bind_param('i', $piece_id);
    $st->execute();
}

/**
 * sets user role to place
 * @param string $token user unique identifier
 */

function change_role_place($token)
{
    global $mysqli;
    $sql = 'UPDATE players SET `role`="place" WHERE token=?';
    $st = $mysqli->prepare($sql);
    $st->bind_param('s', $token);
    $st->execute();
}

/**
 * checks if player meets all requirements to place piece
 * @param $input json  
 * and then calls do_place_piece
 */

function place_piece($input)
{
    if ($input['token'] == null || $input['token'] == '') {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "token is not set."]);
        exit;
    }
    $status = read_status();
    if ($status['status'] != 'started') {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "Game is not in action."]);
        exit;
    }
    if ($status['p_turn'] != get_player_id($input['token'])) {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "It is not your turn."]);
        exit;
    }
    if (check_empty_square($input['x'], $input['y'])) {
        do_place_piece($input);
    } else {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "You cant place your piece here."]);
    }
    exit;
}

/**
 * check if board square is empty
 * square can contain either null or piece_id
 * @param int $x horizontal axis
 * @param int $y vertical axis
 * @return boolean 
 */

function check_empty_square($x, $y)
{
    global $mysqli;
    $sql = 'select count(piece) as c  from board where x=? and y=?';
    $st = $mysqli->prepare($sql);
    $st->bind_param('ii', $x, $y);
    $st->execute();
    $res = $st->get_result();
    $count = $res->fetch_assoc();
    if ($count['c'] > 0) {
        return false;
    } else {
        return true;
    }
}

/**
 * picks the piece for other player to place
 * @param int $piece_id contains the id of the piece
 * id is [1-16] 
 * calls curent_selected_piece
 * calls change_role
 */

function do_place_piece($input)
{
    global $mysqli;
    $sql = 'call `place_piece`(?,?,?);';
    $st = $mysqli->prepare($sql);
    $st->bind_param('iii', $input['x'], $input['y'], curent_selected_piece());
    $st->execute();
    if (check_win($input['x'], $input['y'])) {
        global $mysqli;
        $sql1 = 'UPDATE `game_status` SET `result`="W",`status`="ended"';
        $st1 = $mysqli->prepare($sql1);
        $st1->execute();
    } else if (check_draw()) {
        global $mysqli;
        $sql = 'UPDATE `game_status` SET `result`="D",`status`="ended"';
        $st = $mysqli->prepare($sql);
        $st->execute();
    } else {
        change_role_to_pick($input['token']);
        header('Content-type: application/json');
        print json_encode(read_board(), JSON_PRETTY_PRINT);
    }
}

/**
 * checks if 16 pieces have been placed on the board and no player has won
 * therefore game draw
 * @return boolean if game draw
 */

function check_draw()
{
    global $mysqli;
    $sql = 'select count(*) as p from board where piece is not null';
    $st = $mysqli->prepare($sql);
    $st->execute();
    $res = $st->get_result();
    $count_piece = $res->fetch_assoc();
    if ($count_piece['p'] == 16) {
        return true;
    } else {
        return false;
    }
}

/**
 * finds current selected piece from game status table
 * @return int $res is piece_id
 */

function curent_selected_piece()
{
    global $mysqli;
    $sql = 'select current_piece from game_status ';
    $st = $mysqli->prepare($sql);
    $st->execute();
    $res = $st->get_result();
    $current_piece = $res->fetch_assoc();
    return $current_piece['current_piece'];
}

/**
 * sets user role to pick
 * @param string $token user unique identifier
 */

function change_role_to_pick($token)
{
    global $mysqli;
    $sql = 'UPDATE players SET `role`="pick" WHERE token=?';
    $st = $mysqli->prepare($sql);
    $st->bind_param('s', $token);
    $st->execute();
}

/**
 * finds the next player and stores the information to game status table
 * @param string $token user unique identifier
 */

function next_player($token)
{
    global $mysqli;
    $sql = 'SELECT player_id from players  where token!=?';
    $st = $mysqli->prepare($sql);
    $st->bind_param('s', $token);
    $st->execute();
    $res = $st->get_result();
    $id = $res->fetch_assoc();

    $sql = 'UPDATE game_status SET p_turn=?';
    $st = $mysqli->prepare($sql);
    $st->bind_param('s', $id['player_id']);
    $st->execute();
}

/**
 * checks if curent placement of a piece wins the game
 * @param string $x    
 * @param string $y
 * if the pieces on a [row/coloum/diagonal]  much one of the arrays included in the $attr_array
 * that means that the piece have at least one common attribute and therefore means that current
 * placement wins the game
 *
 *cals horizontal_pieces , vertical_pieces , check_left_diagonal_pieces , check_right_diagonal_pieces
 */

function check_win($x, $y)
{
    $attr_array = array(
        array(1, 2, 3, 4, 5, 6, 7, 8),
        array(9, 10, 11, 12, 13, 14, 15, 16),
        array(5, 6, 7, 8, 13, 14, 15, 16),
        array(1, 2, 3, 4, 9, 10, 11, 12),
        array(2, 4, 6, 8, 10, 12, 14, 16),
        array(1, 3, 5, 7, 9, 11, 13, 15),
        array(3, 4, 7, 8, 11, 12, 15, 16),
        array(1, 2, 5, 6, 9, 10, 13, 14)
    );

    $hp = horizontal_pieces($x);
    $vp = vertical_pieces($y);

    if ($x == $y) {
        $flag = "l";
        $ldp = check_left_diagonal_pieces();
        $possible_win_line = array($hp, $vp, $ldp);
    } elseif ($x + $y == 5) {
        $flag = "r";
        $rdp = check_right_diagonal_pieces();
        $possible_win_line = array($hp, $vp, $rdp);
    } else {
        $possible_win_line = array($hp, $vp);
    }

    for ($i = 0; $i < count($possible_win_line); $i++) {
        for ($j = 0; $j < count($attr_array); $j++) {
            if (count(array_intersect($possible_win_line[$i], $attr_array[$j])) == 4) {
                set_win_combination($i, $flag, $x, $y);
                return true;
            }
        }
    }
    return false;
}

/**
 * sets the winning combination of the board
 * @param int $i
 * @param string $flag
 * @param string $x    
 * @param string $y
 * 
 */

function set_win_combination($i, $flag, $x, $y)
{
    switch ($i) {
        case 0:
            $combination = "horizontal" . $x;
            break;
        case 1:
            $combination = "vertical" . $y;
            break;
        case 2:
            if ($flag == "l") {
                $combination = "left diagonal_";
            } else {
                $combination = "right diagonal_";
            }
    }
    global $mysqli;
    $sql = 'UPDATE game_status SET win_combination=?';
    $st = $mysqli->prepare($sql);
    $st->bind_param('s', $combination);
    $st->execute();
}

/**
 * reads all pieces on the horizontal(x) axis
 * @param string $x    
 * @param string $y
 * @return array $res
 * 
 */

function horizontal_pieces($x)
{

    $result = array();
    for ($i = 1; $i <= 4; $i++) {
        global $mysqli;
        $sql = 'select piece from board where x=? and y=?';
        $st = $mysqli->prepare($sql);
        $st->bind_param('ii', $x, $i);
        $st->execute();
        $res = $st->get_result();
        $res1 = $res->fetch_assoc();
        array_push($result, $res1['piece']);
    }
    return $result;
}

/**
 * reads all pieces on the vertical(y) axis
 * @param string $x    
 * @param string $y
 * @return array $res
 * 
 */

function vertical_pieces($y)
{
    $result = array();
    for ($i = 1; $i <= 4; $i++) {
        global $mysqli;
        $sql = 'select piece from board where x=? and y=?';
        $st = $mysqli->prepare($sql);
        $st->bind_param('ii', $i, $y);
        $st->execute();
        $res = $st->get_result();
        $res1 = $res->fetch_assoc();
        array_push($result, $res1['piece']);
    }
    return $result;
}

/**
 * reads all pieces on the left diagonal 
 * @param string $x    
 * @param string $y
 * @return array $res
 * 
 */


function check_left_diagonal_pieces()
{
    $result = array();
    for ($i = 1; $i <= 4; $i++) {
        for ($j = 1; $j <= 4; $j++) {
            if ($i == $j) {
                global $mysqli;
                $sql = 'select piece from board where x=? and y=?';
                $st = $mysqli->prepare($sql);
                $st->bind_param('ii', $i, $j);
                $st->execute();
                $res = $st->get_result();
                if ($res) {
                    $res1 = $res->fetch_assoc();
                    array_push($result, $res1['piece']);
                } else {
                    array_push($result, 0);
                }
            }
        }
    }
    return $result;
}

/**
 * reads all pieces on the right diagonal 
 * @param string $x    
 * @param string $y
 * @return array $res
 * 
 */

function check_right_diagonal_pieces()
{
    $result = array();
    for ($i = 1; $i <= 4; $i++) {
        for ($j = 1; $j <= 4; $j++) {
            if ($i + $j == 5) {
                global $mysqli;
                $sql = 'select piece from board where x=? and y=?';
                $st = $mysqli->prepare($sql);
                $st->bind_param('ii', $i, $j);
                $st->execute();
                $res = $st->get_result();
                if ($res) {
                    $res1 = $res->fetch_assoc();
                    array_push($result, $res1['piece']);
                } else {
                    array_push($result, 0);
                }
            }
        }
    }
    return $result;
}
?>