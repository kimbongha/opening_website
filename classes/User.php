<?php

	/**
	* Classe représentant un utilisateur du site
	*/
	class User implements JsonSerializable
	{
		/** 
	     * Id unique de l'utilisateur
	     *
	     * @var int
	     * @access private
	     */
	    private $id;

	    /** 
	     * Mail de l'utilisateur
	     *
	     * @var string
	     * @access private
	     */
		private $mail;

		/** 
	     * Mail de l'utilisateur à la création du compte
	     *
	     * @var string
	     * @access private
	     */
		private $mail_at_account_creation;

		/** 
	     * Prénom de l'utilisateur
	     *
	     * @var string
	     * @access private
	     */
		private $firstname;

		/** 
	     * Nom de l'utilisateur
	     *
	     * @var string
	     * @access private
	     */
		private $name;

	    /** 
	     * Statut de l'utilisateur
	     * 2 : visiteur
	     * 3 : adhérent
	     * 4 : auteur
	     * 5 : admin
	     *
	     * @var int
	     * @access private
	     */
		private $status;

		/** 
	     * Date de paiement de l'adhésion de l'utilisateur
	     * L'accès au site est réservé aux utilisateurs dont la cotisation est à jour, ayant donc une date de paiement de moins d'un an
	     * Format YYYY-MM-DD
	     *
	     * @var date
	     * @access private
	     */
		private $subscription_date;

		/**
		* Constructeur de la classe
		*
		* @param void
		*/
		function __construct($id, $mail, $status, $subscription_date, $firstname = NULL, $name = NULL, $first_mail = NULL)
		{
			$this->id = $id;
			$this->mail = $mail;			
			$this->status = (int) $status;
			$this->subscription_date = $subscription_date;
			$this->firstname = $firstname;
			$this->name = $name;
			$this->mail_at_account_creation = $first_mail;
		}


		/**
		* Retourne la valeur de l'id de l'utilisateur
		*
		* @param int
		*/
		public function getUserID()
		{
			return $this->id;
		}

		/**
		* Retourne le mail de l'utilisateur
		*
		* @param string
		*/
		public function getUserMail()
		{
			return $this->mail;
		}

		/**
		* Retourne le mail de l'utilisateur à la création du compte
		*
		* @param string
		*/
		public function getUserFirstMail()
		{
			return $this->mail_at_account_creation;
		}

		/**
		* Retourne le prénom de l'utilisateur
		*
		* @param string
		*/
		public function getUserFirstname()
		{
			return $this->firstname;
		}

		/**
		* Retourne le nom de l'utilisateur
		*
		* @param string
		*/
		public function getUserName()
		{
			return $this->name;
		}

		/**
		* Retourne le statut de l'utilisateur
		* 2 : visiteur
	    * 3 : adhérent
	    * 4 : auteur
	    * 5 : admin
		*
		* @param int
		*/
		public function getUserStatus()
		{
			return (int) $this->status;
		}

		/**
		* Retourne la date de paiement de l'adhésion de l'utilisateur
		*
		* @param date
		*/
		public function getUserSubscriptionDate()
		{
			return $this->subscription_date;
		}

		/**
		* Change le mail l'utilisateur
		*
		* @param void
		*/
		public function setUserMail($mail)
		{
			$this->mail = $mail;
		}

		/**
		* Change le prénom de l'utilisateur
		*
		* @param void
		*/
		public function setUserFirstname($firstname)
		{
			$this->firstname = $firstname;
		}

		/**
		* Change le nom de l'utilisateur
		*
		* @param void
		*/
		public function setUserName($name)
		{
			$this->name = $name;
		}

		/**
		* Change le statut de l'utilisateur
		*
		* @param void
		*/
		public function setUserStatus($status)
		{
			$this->status = (int) $status;
		}

		/**
		* Change la date de paiement de l'adhésion de l'utilisateur
		*
		* @param void
		*/
		public function setUserSubscriptionDate($subscription_date)
		{
			$this->subscription_date = $subscription_date;
		}

		/**
		* Donnes une représentation de cet utilisateur sous forme de string
		*
		* @param void
		*/
		public function toString()
		{
			return "User - id=$this->id; mail=$this->mail; mail_at_account_creation=$this->mail_at_account_creation; firstname=$this->firstname; name=$this->name; status=$this->status; subscription_date=$this->subscription_date";
		}

		public function toArray() 
		{
			return array(
    	    	'id' => $this->id,
				'mail' => $this->mail,
				'status' => $this->status,
				'subscription_date' => $this->subscription_date,
				'firstname' => $this->firstname,
				'name' => $this->name,
				'mail_at_account_creation' => $this->mail_at_account_creation
    	    );
		}

		public function jsonSerialize () 
		{
    	    return $this->toArray();
    	}
	}

?>