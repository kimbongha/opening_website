<?php

	function createUser($new_user_mail, $new_user_type, $new_user_sub_date, $new_user_firstname = NULL, $new_user_name = NULL)
	{
		//on crée un compte que pour un mail valide
		if (filter_var($new_user_mail, FILTER_VALIDATE_EMAIL)) {
		    $sql = SQL::getInstance();
		    $conn = $sql->getBoolConnexion();
			//Le mail est bien à un format valide			
			$user = unserialize($sql->getUserByExactMail($new_user_mail));	
			if ($user != null) {
				//ce mail est déjà associé à un compte!
				//$msg_new_user
				echo "Erreur lors de la création du compte : un compte existe déjà avec cette adresse mail: $new_user_mail";
				return false;
			} else {
				//aucun compte n'existe avec cette adresse
				$date_format = '%Y-%m-%d';
				if (strptime($new_user_sub_date, $date_format)) {
					//Date valide, tout est bon on peut créer notre user!	
					$new_user = new User(0, $new_user_mail, $new_user_type, $new_user_sub_date, $new_user_firstname, $new_user_name);	
					$new_password = $sql->generatePassword();	
			
					$sql->addUser($new_user, $new_password);
					
					$msg_new_user = "Un nouvel utilisateur a bien été créé, son mail est : ".$new_user_mail." il a le statut=".$new_user_type;
					//Pour gérer les fichiers il y a besoin de les include
					/*$path = '/home/openingbqo/opening_website_assets/';
					set_include_path(get_include_path() . PATH_SEPARATOR . $path);
					//Dans un gros fichier complet
					$myfile = fopen("mdp.txt", "a+") or die("Unable to open file!");
					fwrite($myfile, "name=".$new_user_mail." password=".$new_password."\r\n");
					*/

					// Envoi d'un mail pour activer le compte avec le mdp généré, et invitation à le changer
					// Préparation du mail contenant le lien d'activation
					$destinataire = $new_user_mail;
					$sujet = "Votre compte OPENING BOOK" ;
				    $headers ='From: noreply@opening-book.eu'."\n";
					$headers = $headers."Content-Type: text/html; charset=UTF-8\n";
                                        $headers .='Content-Transfer-Encoding: 8bit';
									
					//Message de confirmation
					$message = '<PRE style="font-size:14px;">'."Vous êtes désormais inscrit sur le site d'OPENING, en tant que cotisant à l'association. Votre adhésion expirera le $new_user_sub_date.\n
Voici votre mot de passe : $new_password\n
Je vous conseille de le modifier dès votre première visite sur notre site.\n
Pour modifier votre mot de passe, identifiez-vous sur <a href='https://opening-book.com/index.php'>opening-book.com</a> et allez sur la page 'Gestion de votre compte'\n
\n
Nous vous souhaitons une agréable consultation de notre collection.\n
\n
---------------\n
Ceci est un mail automatique, Merci de ne pas y répondre.\n".'</PRE>'.'<img style="float: right;"'." src='https://alpha.opening-book.eu/assets/logo.png' width='80px' height='47px'>";

					mail($destinataire, '=?UTF-8?B?'.base64_encode($sujet).'?=', $message, $headers) ; // Envoi du mail
					return true;
				} else {
					//Date invalide
					echo "Erreur lors de la création du compte : la date spécifiée est incorrecte";
					return false;
				}			
			}						
		} else { 
			echo "Erreur lors de la création du compte : l'adresse mail spécifiée est invalide";
			return false;
		}
	}

    $lang = setLanguage();

    $sql = SQL::getInstance();
    $conn = $sql->getBoolConnexion();
    
    session_start();    
    $logged = isset($_SESSION['logged']) ? $_SESSION['logged'] : false;
    $user_logged = (isset($_SESSION['user_logged'])) ? $_SESSION['user_logged'] : false;

	if (isset($_POST['new_user_form'])) {	
		$new_user_mail = stripslashes($_POST['mail']);
		$new_user_sub_date = $_POST['subscripion_end_date'];
		$new_user_type = $_POST['user_type'];
		$new_user_firstname = isset($_POST['firstname']) ? $_POST['firstname'] : NULL;
		$new_user_name = isset($_POST['name']) ? $_POST['name'] : NULL;

		createUser($new_user_mail, $new_user_type, $new_user_sub_date, $new_user_firstname, $new_user_name);
	}

	if (isset($_POST['search_user_form'])) {
		$mail_searched = stripslashes($_POST['mail']);
		$retrieved_users = $sql->getUserByMail('%'.$mail_searched.'%');	

		if ($retrieved_users != null) {
			//Trouvé!
			$msg_user_search = "";
			$msg_user_search = "réussi<br>";
			$json_retrieved_users = json_encode($retrieved_users);
			//foreach ($retrieved_users as $user) {
				//$msg_user_search = $msg_user_search.$user->toString()."<br>";
		} else {
			//aucun compte n'existe avec cette adresse
			//faire variable booléenne qui vaut true si on affiche un message
			//le message est définie dans la constanste...
			$msg_user_search = "Pas d'utilisateur correspondant trouvé";
		}
	}

	if (isset($_POST['search_author_form'])) {
		$pseudo_searched = stripslashes($_POST['author_pseudo']);
		$retrieved_authors = $sql->getAuthorsByName('%'.$pseudo_searched.'%');

		if ($retrieved_authors != null) {
			//Trouvé!
			$msg_author_search = "";
			foreach ($retrieved_authors as $index => $author) {
				$msg_author_search = $msg_author_search.$author->toString()."<br>";
			}
		} else {
			//aucun compte n'existe avec cette adresse
			//faire variable booléenne qui vaut true si on affiche un message
			//le message est définie dans la constanste...
			$msg_author_search = "Pas d'auteur correspondant trouvé";
		}
	}

	if (isset($_POST['new_artist_form'])) {
		/*echo isset($_POST['artist_cv_file']);
		echo "<br>y avait un fichier?";*/
		$new_author_msg = "";
		$cv_filename = NULL;
		$description_filename_fr = NULL;
		$description_filename_en = NULL;

		$file_upload_success_sofar = true;

		if (isset($_POST['artist_cv_file'])) {
			if ($_FILES['artist_cv_file']['error'] > 0) {
				$file_upload_success_sofar = false;
			} else {
				$cv_extension = strtolower(substr(strrchr($_FILES['artist_cv_file']['name'], '.'), 1));
				if ($cv_extension != "pdf") {
					$incorrect_file_extension_error = true;
				} else {
					$cv_filename = $_FILES['artist_cv_file']['name'];
					$path = "assets/cv/".$cv_filename;
					$move_file = move_uploaded_file($_FILES['artist_cv_file']['tmp_name'], $path);
					if (!$move_file) {
						$file_upload_success_sofar = false;
					}
				}
			}
		}

		if ($_FILES['artist_description_file_fr']['error'] > 0) {
			$file_upload_success_sofar = false;
		} else {
			$description_extension = strtolower(substr(strrchr($_FILES['artist_description_file_fr']['name'], '.')  ,1)  );
			if ($description_extension != "txt") {
				$incorrect_file_extension_error = true;
			} else {
				$description_filename_fr = $_FILES['artist_description_file_fr']['name'];
				$path = "assets/artists_descriptions/fr/".$description_filename_fr;
				$move_file = move_uploaded_file($_FILES['artist_description_file_fr']['tmp_name'], $path);
				if (!$move_file) {
					$file_upload_success_sofar = false;
				}
			}			
		}

		//rebelote en anglais
		if ($_FILES['artist_description_file_en']['error'] > 0) {
			$file_upload_success_sofar = false;
		} else {
			$description_extension = strtolower(substr(strrchr($_FILES['artist_description_file_en']['name'], '.')  ,1)  );
			if ($description_extension != "txt") {
				$incorrect_file_extension_error = true;
			} else {
				$description_filename_en = $_FILES['artist_description_file_en']['name'];
				$path = "assets/artists_descriptions/en/".$description_filename_en;
				$move_file = move_uploaded_file($_FILES['artist_description_file_en']['tmp_name'], $path);
				if (!$move_file) {
					$file_upload_success_sofar = false;
				}
			}			
		}

		if (strcmp($description_filename_fr, $description_filename_en) != 0) {
			$file_upload_success_sofar = false;
			$new_author_msg .= "Erreur : les 2 fichiers de description doivent avoir le même nom<br>";
		}

		if ($file_upload_success_sofar) {
			//files correctly uploaded, we go on
			$new_user_mail = stripslashes($_POST['mail']);
			$new_user_sub_date = $_POST['subscripion_end_date'];
			//Si on utilise le formulaire, c'est pour créer un auteur
			$new_user_type = 4;
			$new_user_firstname = isset($_POST['firstname']) ? $_POST['firstname'] : NULL;
			$new_user_name = $_POST['name'];

			$isUserCreated = createUser($new_user_mail, $new_user_type, $new_user_sub_date, $new_user_firstname, $new_user_name);

			if ($isUserCreated) {
			 	$user = unserialize($sql->getUserByExactMail($new_user_mail));
			 	$user_id = $user->getUserID();

			 	$new_author = new Author(0, $_POST['artist_name'], $user_id, $new_user_name, $description_filename_fr, $cv_filename);
				$success = $sql->addAuthor($new_author);
				if ($success) {
					$new_author_msg  .= "Succès : creation du compte artiste réussi";
				} else {
					$new_author_msg .= "Echec lors de la creation du compte artiste";
				}
			} else {
				$new_author_msg .= "Echec lors de la creation du compte user de l'artiste";
			}
		 } else {
		 	$new_author_msg .= "Echec lors de l'upload des fichiers de l'artiste";
		 }
	}

	if (isset($_POST['new_book_form'])) {
		$new_book_msg = "Haha je t'ai vu t'as cliqué! Bon désolé en fait ça rien pour l'instant...";
		/*
		if ($_FILES['full_book_file']['error'] > 0 and $_FILES['extract_book_file']['error'] > 0) {
			$dl_fail_error = true;
		} else {
			$full_book_extension = strtolower(substr(strrchr($_FILES['full_book_file']['name'], '.')  ,1)  );
			$book_extract_extension = strtolower(substr(strrchr($_FILES['extract_book_file']['name'], '.')  ,1)  );
			if ($full_book_extension != "pdf" and $book_extract_extension != "pdf") {
				$incorrect_file_extension_error = true;
			} else {
				$book_name = $_FILES['full_book_file']['name'];
				//$book_extract_name = $_FILES['extract_book_file']['name'];
				$full_book_path = "bbff/".$book_name;
				//Le nom du fichier est le même pour les deux, seul le dossier change
				$book_extract_path = "assets/extracts/".$book_name;
			}			
		}
		*/
	}

	if (isset($_POST['set_lang_files_form'])) {
		if ($_FILES['fr_lang_file']['error'] > 0 and $_FILES['en_lang_file']['error'] > 0) {
			//rajouter une condition par fichier de langue
			$dl_fail_error = true;
		} else {
			$fr_lang_file_extension = strtolower(substr(strrchr($_FILES['fr_lang_file']['name'], '.')  ,1)  );
			$en_lang_file_extension = strtolower(substr(strrchr($_FILES['en_lang_file']['name'], '.')  ,1)  );
			/*$de_lang_file_extension = strtolower(substr(strrchr($_FILES['de_lang_file']['name'], '.')  ,1)  );
			$es_lang_file_extension = strtolower(substr(strrchr($_FILES['es_lang_file']['name'], '.')  ,1)  );
			$it_lang_file_extension = strtolower(substr(strrchr($_FILES['it_lang_file']['name'], '.')  ,1)  );*/

			if ($fr_lang_file_extension != "php" and $en_lang_file_extension != "php") {
				//rajouter une condition par fichier de langue
				$incorrect_file_extension_error = true;
			} else {
				$lang_file_name = "-lang.php";
				$folder_path = "views/include/";

				$move_fr_file = move_uploaded_file($_FILES['fr_lang_file']['tmp_name'], $folder_path."fr".$lang_file_name);
				$move_en_file = move_uploaded_file($_FILES['en_lang_file']['tmp_name'], $folder_path."en".$lang_file_name);
				/*$move_de_file = move_uploaded_file($_FILES['de_lang_file']['tmp_name'], $folder_path."de".$lang_file_name);
				$move_es_file = move_uploaded_file($_FILES['es_lang_file']['tmp_name'], $folder_path."es".$lang_file_name);
				$move_it_file = move_uploaded_file($_FILES['it_lang_file']['tmp_name'], $folder_path."it".$lang_file_name);*/
				echo "DL réussi et extensions correctes - fichiers de langues mis à jour<br>";
			}			
		}		
	}

	include_once('./views/admin.php');
	
?>						