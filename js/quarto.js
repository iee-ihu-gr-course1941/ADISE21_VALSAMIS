var me = { username: null, player_id: null, token: null, role: null, last_action: null };
var game_status = { status: null, p_turn: null, current_piece: null, result: null, win_combination: null, last_change: null };
var last_update = new Date().getTime();
var timer = null;

$(function () {
	draw_empty_board();
	$('#piece_selector_input').hide();
	$('#piece_coordinates_input').hide();
	$('#waiting').hide();
	$('#winner').hide();
	$('#loser').hide();
	$('#draw').hide();
	$('#aborted').hide();
	$('#remaining_pieces').hide();

	$('#quatro_login').click(login_to_game);
	$('#start_reset_game').click(reset_game);
	$('#piece_selected').click(pick);
	$('#place_piece').click(do_place);

});

/**
 * draws board on webpage
 * creates table and inserts it in a div dynamically   
 **/

function draw_empty_board() {
	var t = '<table id="quarto_table">';
	for (var i = 4; i > 0; i--) {
		t += '<tr>';
		for (var j = 1; j <= 4; j++) {
			t += '<td  class="quarto_square" id="square_' + i + '_' + j + '"> <img class="piece"  src="images/board.jpg"></BR> ' + i + ',' + j + '  </img></td>';
		}
		t += '</tr>';
	}
	t += '</table>';
	$('#quarto_board').html(t);
}

/**
 * makes HTTP request for player login 
 */

function login_to_game() {
	if ($('#username').val() == '') {
		alert('You have to set a username');
		return;
	}
	draw_empty_board();
	$.ajax({
		url: "quarto.php/players/login/",
		method: 'PUT',
		dataType: "json",
		headers: { "X-Token": me.token },
		contentType: 'application/json',
		data: JSON.stringify({ username: $('#username').val() }),
		success: login_result,
		error: login_error
	});
}

/**
 * stores the player info localy for future use
 * @param {json} data 
 * calls update_info
 */

function login_result(data) {
	me = data[0];
	$("#game_login_input").hide();
	piece_list();
	update_info();
	game_status_update();
}

/**
 * error information handling for login request
 * @param {json} data
 */

function login_error(data) {
	var x = data.responseJSON;
	alert(x.errormesg);
}

/**
 * updates the game status and user information
 * used for debug purposes
 * 
 */

function update_info() {
	$('#player_info').html("<h4>Player info</h4><strong> Username:</strong>"
		+ me.username + "<strong> id: </strong>"
		+ me.player_id + "<strong> token: </strong>"
		+ me.token + "<strong> Player role: </strong> "
		+ me.last_action + "<br><h4>Game info</h4><strong> Game state: </strong>"
		+ game_status.status + "<strong> Player turn: </strong>"
		+ game_status.p_turn + "<strong> Current Piece: </strong>"
		+ game_status.current_piece + "<strong> Result: </strong>"
		+ game_status.result + "<strong> win_combination: </strong>"
		+ game_status.win_combination + "<strong> Last_change: </strong>"
		+ game_status.last_change);
}

/**
 * makes HTTP request and gets the current game status
 * calls update_status
 */

function game_status_update() {
	clearTimeout(timer);
	$.ajax({
		url: "quarto.php/status/",
		method: 'GET',
		success: update_status,
		headers: { "X-Token": me.token }
	});
}

/**
 * makes HTTP request to get player info
 * updates local player info in case of changes in the database
 */

function update_user() {
	$.ajax({
		url: "quarto.php/players/login/",
		method: 'GET',
		dataType: "json",
		headers: { "X-Token": me.token },
		contentType: 'application/json',
		success: save_player_info
	});

}

/**
 * updates  local users info
 * stores the changes
 * @param {json} data
 */

function save_player_info(data) {
	me = data[0];
}

/**
 * enables and disables the ui elements responsible for gameplay
 * according to player turn and role
 * 
 * turn is defined with player id
 * role can be either pick or place 
 * checks for possible win,lose,draw and informs the user
 * @param {json} data
 */

function update_status(data) {
	last_update = new Date().getTime();
	game_status = data[0];

	if (game_status.status == "aborted") {
		$('#piece_selector_input').hide();
		$('#piece_coordinates_input').hide();
		$('#waiting').hide();
		update_info();
		$('#aborted').show(1000);
	} else {
		update_info();
		if (game_status.result == "W") {
			$('#piece_selector_input').hide();
			$('#piece_coordinates_input').hide();
			$('#waiting').hide();
			if (game_status.p_turn != me.player_id) {
				$flag = "lose";
				highlight_winning_pieces(game_status.win_combination, $flag);
				$('#loser').show(1000);
			} else {
				$flag = "win";
				highlight_winning_pieces(game_status.win_combination, $flag);
				$('#winner').show(1000);
			}
			fill_board();
			update_info();
			exit();
		} else if (game_status.result == "D") {
			$('#piece_selector_input').hide();
			$('#piece_coordinates_input').hide();
			$('#waiting').hide();
			$('#draw').show(1000);
			fill_board();
			update_info();
			exit();
		}
		update_user();
		update_info();
		clearTimeout(timer);
	if (game_status.p_turn == me.player_id && me.role != null) {
			fill_board();
			if (me.role == "pick") {
				$('#piece_selector_input').show(1000);
				 $('#text_input_pick').show(1000);
				$('#piece_coordinates_input').hide(1000);
				timer = setTimeout(function () { game_status_update(); }, 1000);
			} else {
				$('#waiting').hide();
				current_piece();
				piece_list();
				$('#piece_coordinates_input').show(1000);
				 $('#text_input_place').show(1000);
				$('#remaining_pieces').show(1000);
				timer = setTimeout(function () { game_status_update(); }, 1000);
			}
		} else {
			fill_board();
			$('#waiting').show();
			$('#piece_selector_input').hide(1000);
			$('#piece_coordinates_input').hide(1000);
			timer = setTimeout(function () { game_status_update(); }, 1000);
		}
	}
}

/**
 *Changes background color to winning squares
 *  @param {string} win_combination
 * * @param {string} flag
 */

function highlight_winning_pieces(win_combination, flag) {

	if (flag == "win") {
		color = '#2da631';
	} else {
		color = '#FC7E7E';
	}
	combination = win_combination.substring(0, win_combination.length - 1);
	switch (combination) {
		case 'vertical':
			var y = win_combination.charAt(win_combination.length - 1);
			for (var x = 1; x <= 4; x++) {
				var id = '#square_' + x + '_' + y;

				$(id).css('background-color', color);
			}
			break;
		case 'horizontal':
			var x = win_combination.charAt(win_combination.length - 1);
			for (var y = 1; y <= 4; y++) {
				var id = '#square_' + x + '_' + y;
				$(id).css('background-color', color);
			}
			break;
		case 'left diagonal':
			for (var i = 1; i <= 4; i++) {
				for (j = 1; j <= 4; j++) {
					if (i == j) {
						var id = '#square_' + i + '_' + j;
						$(id).css('background-color', color);
					}
				}
			}
			break;
		case "right diagonal":

			for (i = 1; i <= 4; i++) {
				for (j = 1; j <= 4; j++) {
					if (i + j == 5) {
						var id = '#square_' + i + '_' + j;
						$(id).css('background-color', color);
					}
				}
			}
			break;
		default: break;
	}
}

/**
 *makes HTTP request to get current state of board
 * calls fill_board_by_data  
 */

function fill_board() {
	$.ajax({
		url: "quarto.php/board/",
		headers: { "X-Token": me.token },
		success: fill_board_by_data
	});
}

/**
 *fills board table dynamically with given state of board 
 *and adds image representation of the piece
 * @param {json} data
 */

function fill_board_by_data(data) {

	for (var i = 0; i < data.length; i++) {
		var board = data[i];
		var id = '#square_' + board.x + '_' + board.y;
		if (board.piece == null) {
			var im = '<img class="piece" src="images/board.jpg"></BR> ' + board.x + ',' + board.y + '  </img>';
		} else {
			var im = '<img class="piece" src="images/piece' + board.piece + '.jpg"></BR> ' + board.x + ',' + board.y + '  </img>';
		}
		$(id).html(im);
	}
}

/**
 *shows the selected piece that the player has to place
 */

function current_piece() {
	$('#curent_piece_img').html("<img src=\"images/piece" + game_status.current_piece + ".jpg\"></img>");
}

/**
* makes HTTP request to pick piece
* sends id of selected piece
*/

function pick() {
	var p_id = $('#piece_selector').val();

	$.ajax({
		url: "quarto.php/board/piece/pick/",
		method: 'PUT',
		dataType: "json",
		contentType: 'application/json',
		data: JSON.stringify({ piece_id: p_id }),
		headers: { "X-Token": me.token },
		success: pick_result,
		error: pick_error
	});
}

/**
* if place request succesful 
* updates status
* @param {json} data
*/

function pick_result(data) {
	game_status_update();
}

/**
*error information handling
* @param {json} data
*/

function pick_error(data) {
	var x = data.responseJSON;
	alert(x.errormesg);
	console.log(data);
}

/**
 * makes HTTP request to place piece
 * sends x , y  coordinates coresponding to board cell
 */

function do_place() {
	$('.quarto_square').css('background-color', 'rgb(238, 237, 237)');
	empty_piece_list();
	piece_list();
	var s = $('#piece_coordinates').val();
	var a = s.trim().split(/[ ]+/);
	if (a.length != 2) {
		alert('Must give 2 numbers');
		return;
	}
	$.ajax({
		url: "quarto.php/board/piece/place/",
		method: 'PUT',
		dataType: "json",
		contentType: 'application/json',
		data: JSON.stringify({ x: a[0], y: a[1] }),
		headers: { "X-Token": me.token },
		success: place_result,
		error: place_error
	});

}

/**
 * if place request succesful 
 * updates status, board condent
 */

function place_result(data) {
	game_status_update();
	fill_board_by_data(data);
}

/**
 *error information handling
 * @param {json} data
 */

function place_error(data) {
	var x = data.responseJSON;
	alert(x.errormesg);

}

/**
 *makes HTTP request to retrieve  non selected  pieces
 */

function piece_list() {
	$.ajax({
		method: 'GET',
		url: "quarto.php/board/piece/pick",
		contentType: 'application/json',
		headers: { "X-Token": me.token },
		success: update_piece_selector
	});
}

/**
 *erases all options from piece selector 
 *erases all images corresponding to those options
 *in order to be dynamically refilled with new piece list
 */

function empty_piece_list() {
	$('#piece_selector').html("<option value=\"\">---Choose piece---</option>");
	$('#piece_images').html(" ");
}

/**
 *updates selector element on webpage 
 *with all available pieces
 @param {json array} list
 */

function update_piece_selector(list) {
	var piece_list = JSON.parse(list);
	var remaining_piece_options = "";
	for (var i = 0; i < piece_list.length; i++) {
		remaining_piece_options += "<option id=\"option_" + piece_list[i] + "\" value=\"" + piece_list[i] + "\">" + piece_list[i] + "</option>";
	}
	$('#piece_selector').html(remaining_piece_options);
	update_pieces_remaining_images(piece_list);
}

/**
*updates image representation of remaining available pieces 
*@param {json array} list
*/

function update_pieces_remaining_images(list) {
	var remaining_images_place = "<h4>Remaining Pieces </h4><br>";
	var remainig_images_pick = "";
	for (var i = 0; i < list.length; i++) {
		remainig_images_pick += "<img class=\"piece_image\" id=\"" + list[i] + "\" src=\"images/available_pieces/piece" + list[i] + ".jpg\" alt=\"Piece :" + list[i] + "\">";
		remaining_images_place += "<img src=\"images/available_pieces/piece" + list[i] + ".jpg\" alt=\"Piece :" + list[i] + "\">";
	}
	$('#piece_images').html(remainig_images_pick)
	$('#remaining_pieces').html(remaining_images_place);
}

/**
*make HTTP request to reset all essential db tables
*initialises game status,board,players
*/

function reset_game() {
	try {
		$.ajax({
			url: "quarto.php/board/",
			method: "POST",
			success: (function () { location.reload(); }),
			headers: { "X-Token": me.token }
		});
	} catch (exception) {
		location.reload();
	}
}




