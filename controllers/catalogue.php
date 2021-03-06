<?php

    $lang = setLanguage();

    $sql = SQL::getInstance();
    $conn = $sql->getBoolConnexion();
    
    session_start();    
    $logged = isset($_SESSION['logged']) ? $_SESSION['logged'] : false;
    $user_logged = (isset($_SESSION['user_logged'])) ? $_SESSION['user_logged'] : false;

    $thumbnail_content = array();
    $sort_type = "default";
    if (isset($_GET['letter'])) {
        // on a une demande par lettre
        $sort_type = "letter";
        $letter_query_author = $_GET['letter']."%";

        $retrieved_authors = $sql->getAuthorsByName($letter_query_author);
        $retrieved_authors_ids = array();
        foreach ($retrieved_authors as $author) {
            $retrieved_authors_ids[] = $author->getAuthorID();
        }

        $retrieved_books = array();

        foreach ($sql->getAllBooks() as $book) {
            foreach ($book->getBookAuthors() as $book_author_id) {
                if(!in_array($book, $retrieved_books)) {
                    //on vérifie que le book est pas déjà dans l'array
                    //c'est possible s'il a plusieurs auteurs et qu'ils ont été trouvés par la recherhce
                    if (in_array($book_author_id, $retrieved_authors_ids)) {
                        $retrieved_books[] = $book;
                        $authors = array(); //un array contenant les objets auteurs du book
                        foreach ($book->getBookAuthors() as $author_id) {
                            $authors[] = unserialize($sql->getAuthorByID($author_id));
                        }
                        $thumbnail_content[] = array( "book" => $book,
                                                    "authors" => $authors);
                    }
                }
            }
        }

    } elseif (isset($_GET['artist_id'])) {
        $sort_type = "artist_id";
        $artist = unserialize($sql->getAuthorByID($_GET['artist_id']));
        $artist_vignettes = array();

        foreach ($sql->getBooksByAuthor($_GET['artist_id']) as $book) {
            $this_book_authors = array();
            foreach ($book->getBookAuthors() as $this_book_author_id) {
                $this_book_authors[] = unserialize($sql->getAuthorByID($this_book_author_id));
            }
            $artist_vignettes[] = array(    "book" => $book,
                                            "authors" => $this_book_authors);
        }
        # on a fini de récupérer nos books on peut les ordonner

    } elseif (isset($_GET['collection'])) {
        $sort_type = "by_collection";
        $my_collection_vignettes = array();
        $collection = urldecode($_GET['collection']);

        foreach ($sql->getBooksByCollection($collection) as $book) {
            $this_book_authors = array();
            foreach ($book->getBookAuthors() as $this_book_author_id) {
                $this_book_author = unserialize($sql->getAuthorByID($this_book_author_id));
                if ($this_book_author) {
                    $this_book_authors[] = $this_book_author;
                }
            }
            if (!empty($this_book_authors)) {
                $my_collection_vignettes[] = array( "book" => $book,
                                                    "authors" => $this_book_authors,
                                                    "publish_date" => $book->getBookPublishDate());
            }
        }
        function date_sort($a, $b) {
            return strtotime($a['publish_date']) - strtotime($b['publish_date']);
        }

        $my_collection_vignettes_sorted = usort($my_collection_vignettes, "date_sort");
        // # avant sort
        // echo "AVANT SORT<br>";
        // foreach ($my_collection_vignettes as $elem) {
        //     echo $elem['publish_date'];
        //     echo "<br>";
        // }
        // echo "FIN ARRAY<br><br>";

        // $sorted_array = usort($my_collection_vignettes, "date_sort");
        // echo "APRES SORT<br>";
        // foreach ($my_collection_vignettes as $elem) {
        //     echo $elem['publish_date'];
        //     echo "<br>";
        // }
        } else {
            //Accueil de la page catalogue presentation des collections
            $sort_type = "default";

            //Pour la section par collection pour l'accueil
            $collections_vignettes = array();
            $books = $sql->getAllBooksOrderedByPubDate();
            $collections_oderedbypubdate = array();
            foreach ($books as $book) {
                $collection = $book->getBookCollection();
                if (!in_array($collection, $collections_oderedbypubdate)) {
                    $collections_oderedbypubdate[] = $collection;
                    $collections_vignettes[] = array(   "collection" => $collection,
                                                        "book" => $book);
                }
            }

            //Pour la section artiste on affiche TOUS les artistes dans l'ordre alpha
            $index = 0;
            $possible_letters = array();
            foreach ($sql->getAuthorsSortedAlphabetical() as $author) {
                $letter = $author->getAuthorSearchName()[0];
                if (!in_array($letter, $possible_letters)) {
                    $possible_letters[] = $letter;
                }
                //on cherche les books de chaque ariste
                foreach ($sql->getAllBooks() as $book) {
                    $this_book_authors_ids = $book->getBookAuthors();
                    if (in_array($author->getAuthorID(), $this_book_authors_ids)) {
                        $this_book_authors = array();
                        foreach ($this_book_authors_ids as $this_book_author_id) {
                            $this_book_authors[] = unserialize($sql->getAuthorByID($this_book_author_id));
                        }
                        $thumbnail_content[$index] = array( "book" => $book,
                                                            "authors" => $this_book_authors);
                        $index++;
                    }
                }
            }

        /* Première méthode pour obtenir les vignettes des collections
        $available_collections = $sql->getAvalaibleCollections();
        foreach ($available_collections as $collection) {
            //on va mettre l'image d'un book de la collection au hasard
            //POSSIBLE feature si la fct renvoie les books rangés par id croissant, le 1er est bien le 1er et on aura sa vignette
            $books = $sql->getBooksByCollection($collection);
            shuffle($books);
            $book = $books[0];
            //ses auteurs pour la vignette
            $this_book_authors = array();
            foreach ($book->getBookAuthors() as $this_book_author_id) {
                $this_book_authors[] = unserialize($sql->getAuthorByID($this_book_author_id));
            }
            $collections_vignettes[] = array(   "collection" => $collection,
                                                "book" => $book,
                                                "authors" => $this_book_authors);
        }*/

        //section d'une grille aléatoire
        /*
        $all_books = $sql->getAllBooks();
        shuffle($all_books);
        $rand_books = array_slice($all_books, 0, min(16, count($all_books)));*/
    }
     
    include_once('./views/catalogue.php');

?>