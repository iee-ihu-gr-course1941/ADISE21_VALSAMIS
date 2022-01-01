Table of Contents
=================
   * [Εγκατάσταση](#εγκατάσταση)
      * [Απαιτήσεις](#απαιτήσεις)
      * [Οδηγίες Εγκατάστασης](#οδηγίες-εγκατάστασης)
   * [Περιγραφή API](#περιγραφή-api)
      * [Methods](#methods)
         * [Board](#board)
            * [Ανάγνωση Board](#ανάγνωση-board)
            * [Αρχικοποίηση Board](#αρχικοποίηση-board)
         * [Piece](#piece)
            * [Επιλογή Πιονιού](#επιλογή-πιονιού)
            * [Ανανέωση Διαθέσιμων Πιονιών](#ανανέωση-διαθέσιμων-πιονιών)
			* [Τοποθέτηση Πιονιού](#τοποθέτηση-πιονιού)

         * [Player](#player)
			* [Ανάγνωση στοιχείων παικτών](#ανάγνωση-στοιχείων-παικτών)
            * [Ανάγνωση στοιχείων παίκτη](#ανάγνωση-στοιχείων-παίκτη)
            * [Καθορισμός στοιχείων παίκτη](#καθορισμός-στοιχείων-παίκτη)
         * [Status](#status)
            * [Ανάγνωση κατάστασης παιχνιδιού](#ανάγνωση-κατάστασης-παιχνιδιού)
      * [Entities](#entities)
         * [Board](#board-1)
		 * [Pieces](#pieces)
         * [Players](#players)
         * [Game_status](#game_status)


# Demo Page

Μπορείτε να κατεβάσετε τοπικά ή να επισκευτείτε την σελίδα: 
https://users.iee.ihu.gr/~it174869/quarto



# Εγκατάσταση

## Απαιτήσεις

* Apache2
* Mysql Server
* php

## Οδηγίες Εγκατάστασης

 * Κάντε clone το project σε κάποιον φάκελο <br/>
  `$ git clone https://github.com/iee-ihu-gr-course1941/ADISE21_VALSAMIS.git`

 * Βεβαιωθείτε ότι ο φάκελος είναι προσβάσιμος από τον Apache Server. πιθανόν να χρειαστεί να καθορίσετε τις παρακάτω ρυθμίσεις.

 * Θα πρέπει να δημιουργήσετε στην Mysql την βάση με όνομα 'quarto' και να φορτώσετε σε αυτήν την βάση τα δεδομένα από το αρχείο schema.sql

 * Θα πρέπει να φτιάξετε το αρχείο lib/db_upass.php το οποίο να περιέχει:
```
    <?php
	$DB_PASS = 'κωδικός';
	$DB_USER = 'όνομα χρήστη';
    ?>
```

# Περιγραφή Παιχνιδιού

Το Quarto είναι ένα επιτραπέζιο παιχνίδι για δύο παίκτες που εφευρέθηκε από τον Ελβετό μαθηματικό Blaise Müller.

Το παιχνίδι παίζεται σε ταμπλό 4×4.
 Υπάρχουν 16 μοναδικά κομμάτια για να παίξετε, τα οποία μπορεί να έχουν τα χαρακτηριστικά:
```
 - άσπρο  ή μαύρο (ή άλλος συνδυασμός χρωμάτων).
 - ψηλό ή κοντό
 - τετράγωνο ή κυκλικό 
 - βαθουλωτή ή συμπαγής κορυφή.
 ```
Οι παίκτες διαλέγουν ένα κομμάτι το οποίο πρέπει να τοποθετήσει ο αντίπαλος παίκτης στο ταμπλό. Ένας παίκτης κερδίζει τοποθετώντας ένα κομμάτι στον πίνακα που σχηματίζει μια οριζόντια, κάθετη ή διαγώνια σειρά τεσσάρων κομματιών, τα οποία έχουν όλα μια κοινή ιδιότητα (όλα μαύρα, όλα τετράγωνα κ.λπ.).


Η εφαρμογή απαπτύχθηκε μέχρι το σημείο .....(αναφέρετε τι υλοποιήσατε και τι όχι)

## Συντελεστές

ΒΑΛΣΑΜΗΣ ΑΝΤΩΝΙΟΣ : Jquery , PHP API ,  Σχεδιασμός mysql




# Περιγραφή API

## Methods


### Board
#### Ανάγνωση Board

```
GET /board/
```

Επιστρέφει το [Board](#Board).

#### Αρχικοποίηση Board
```
POST /board/
```

Αρχικοποιεί το Board, δηλαδή το παιχνίδι. Γίνονται reset τα πάντα σε σχέση με το παιχνίδι.
Επιστρέφει το [Board](#Board).

### Piece
#### Επιλογή Πιονιύ

```
PUT /board/piece/pick/
```

JSON Data:

| Field             | Description                 | Required   |
| ----------------- | --------------------------- | ---------- |
| `piece_id`        | Μοναδικός Αριθμός Πιονιού   | yes        |

Επιλέγει το πιόνι που θα πρέπει να τοποθετήσει ο αντίπαλος
Ενημερώνει το game status για το current_piece.

#### Ανανέωση Διαθέσιμων Πιονιών

```
GET /board/piece/pick
```
Επιστρέφει τα στοιχεία από το [Pieces](#Pieces) με piece_id.
Ώστε να γνωρίζουν οι παίκτες τα διαθέσιμα πιόνια.

#### Τοποθέτηση Πιονιού

```
PUT /board/piece/place/
```
JSON Data:

| Field             | Description                 | Required   |
| ----------------- | --------------------------- | ---------- |
| `x`        | Η  θέση x  στο board               | yes        |
| `y`        | Η  θέση y  στο board               | yes        |
| `piece_id` | Μοναδικός Aριθμός Πιονιού          | yes        |


### Player

#### Ανάγνωση στοιχείων παικτών

```
GET /players/
```
Επιστρέφει τα στοιχεία όλων των παίκτων του παιχνιδιού.


#### Ανάγνωση στοιχείων παίκτη
```
GET /players/login/
```
Επιστρέφει τα στοιχεία του παίκτη .

#### Καθορισμός στοιχείων παίκτη
```
PUT /players/login/
```
JSON Data:

| Field             | Description                 | Required   |
| ----------------- | --------------------------- | ---------- |
| `username`        | Το username για τον παίκτη. | yes        |


Επιστρέφει τα στοιχεία του παίκτη και ένα token. Το token πρέπει να το χρησιμοποιεί ο παίκτης καθόλη τη διάρκεια του παιχνιδιού.

### Status

#### Ανάγνωση κατάστασης παιχνιδιού
```
GET /status/
```

Επιστρέφει το στοιχείο [Game_status](#Game_status).



## Entities


### Board
---------

Το board είναι ένας πίνακας, ο οποίος στο κάθε στοιχείο έχει τα παρακάτω:


| Attribute                | Description                                  | Values                              |
| ------------------------ | -------------------------------------------- | ----------------------------------- |
| `x`                      | H συντεταγμένη x του τετραγώνου              | 1..4                                |
| `y`                      | H συντεταγμένη y του τετραγώνου              | 1..4                                |
| `piece`                  | To Πιόνι που υπάρχει στο τετράγωνο           | 1..16, null       |



### Pieces
---------

To κάθε κομμάτι έχει τα παρακάτω στοιχεία:

| Attribute                | Description                                  | Values                              |
| ------------------------ | -------------------------------------------- | ----------------- |
| `piece_id` 			   | Μοναδικός  αριθμός						  	  |  1...16           |
| `is_black`               | Άν είναι Μαύρο ή Άσπρο                       | 'TRUE','FALSE'    |                           
| `is_square`              | Αν είναι Τετράγωνο ή Στρογγυλό               | 'TRUE','FALSE'    |
| `is_tall`                | Αν είναι Ψηλό ή Κοντό           			  | 'TRUE','FALSE'    |  
| `is_solid`               | Αν έχει Βαθουλωτή κορυφή ή Συμπαγής κορυφή   | 'TRUE','FALSE'    |
| `available`              | Είδος κίνησης που πρέπει να κάνει ο παίκτης  | 'pick','place'    |


### Players
---------

O κάθε παίκτης έχει τα παρακάτω στοιχεία:

| Attribute                | Description                                  | Values                       |
| ------------------------ | -------------------------------------------- | ---------------------------- |
|`player_id` 			   | Μόναδικος Αύξων Αριθμός				      | ΙΝΤ INCREMENT                |         
|`username`                | Όνομα Παίκτη                                 | String                       |                        
| `token`                  | To κρυφό token του παίκτη.                   | HEX                          |
| `role`                   | Είδος κίνησης που πρέπει να κάνει ο παίκτης  | 'pick','place'               |

### Game_status
---------

H κατάσταση παιχνιδιού έχει τα παρακάτω στοιχεία:

| Attribute                | Description                                  | Values                       |
| ------------------------ | -------------------------------------------- | -----------------------------|
| `status`                 | Κατάσταση  												   |'not active', 'initialized', 'started', 'ended', 'aborted'|
| `p_turn`                 | To token του παίκτη που παίζει                                | HEX         |
| `current_piece`          | Δείχνει το επιλέγμενο πιόνι								   | 1...16		 |
| `result`                 | Καθοριστικό νίκης ή ισοπαλίας                                 |'W','D',null |
| `win_combination`        | Καθορίζει τον συνδιασμό νίκης πάνω στο board		           |'not set','vertical+y','horizontal+x','left diagonal','right diagonal' (x και y τελευταίες συντεταγμένες τοποθέτησης|
| `last_change`            | Τελευταία αλλαγή/ενέργεια στην κατάσταση του παιχνιδιού       | timestamp   |
