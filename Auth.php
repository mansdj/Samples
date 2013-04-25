<?php  

/**
 * Custom authentication model for use in core applications.  There are 
 * some dependencies for this model in regards to user structure, specifically
 * in the database.
 * 
 * ->username = email address (mysql column = email)
 * ->id = md5(email address)
 * ->password = sha1(password, salt);
 *
 * @author David Mans
 * 
 */
final class Auth 
{
	/**
	 * Application wide salt.
	 * 
	 * @var string
	 */
	const SALT = "som3S4lt";		//Placeholder salt, replace in production, should be more cryptic/obfuscated 
	
	/**
	 * @var bool
	 */
	protected $active;
	
	/**
	 * @var User
	 */
	protected $user;
	
	/**
	 * @var Role
	 */
	protected $userRole;
	
	/**
	 * Active getter method
	 * 
	 * @return boolean
	 */
	public function Active()
	{
		return $this->active;
	}
	
	/**
	 * User getter method
	 * 
	 * @return User
	 */
	public function User()
	{
		return $this->user;
	}
	
	/**
	 * Role getter method
	 * @return Role
	 */
	public function Role()
	{
		return $this->userRole;
	}
	
	/**
	 * Authenticating a user based on an email and password being provided.  The 
	 * password may or may not be hashed.  If it is not hashed, the provided 
	 * password will be hashed and verified with the existing password.  Any failure
	 * in the authentication process will yield a false return.
	 * 
	 * @author David Mans
	 * @param string $email
	 * @param string $password
	 * @return boolean
	 */
	public function Authenticate($email, $password)
	{
		//Verify the email address is a standard email address
		if($this->emailValidation($email))
		{
			//Build the query to fetch the user
			$uQuery = new UserQuery();
			
			//Assign the returned user
			$user = $uQuery->findOneByEmail($email);

			//Verify the password is hashed and not empty, react accordingly
			if(strlen($password) == 40)
			{
				//See if the passwords match
				if($user->getPassword() == $password)
				{
					//Assign the the user to the user property
					$this->user = $user;
					
					return true;
				}
				else
				{
					//Passwords don't match
					return false;	
				}
			}
			elseif(!empty($password))
			{
				//Hash the password
				$password = $this->HashPassword($password);
				
				//See if they passwords match
				if($user->getPassword() == $password)
				{
					
					return true;
				}
				else
				{
					//Passwords don't match
					return false;
				}
			}
			else 
			{
				//Password string is empty
				return false;
			}
		}
		else 
		{
			//Email is not a valid email
			return false;
		}
		
	}
	
	/**
	 * Take a string and hash it with a SHA1 encryption.  If the
	 * string passed is empty, a null value will be returned.
	 * 
	 * @author David Mans
	 * @param string $string
	 * @return string
	 */
	public function HashPassword($string)
	{
		//Verify the strings not empty and hash it
		if(!empty($string)) 
		{
			$hash = sha1($string . self::SALT);
		}
		else 
		{
			//string was empty, give it a null value
			$hash = NULL;
		}
		
		return $hash;
	}
	
	/**
	 * Login the user from the perspective of this class.  The user is added 
	 * to the user property.  The role property is assigned based on the user's
	 * role.  We set the user to active, as in he/she is logged in.
	 * 
	 * @author David Mans
	 * @param User $user
	 */
	protected function Login(User $user)
	{
		//Assign the the user to the user property
		$this->user = $user;
			
		//Assign the users role
		$this->userRole = $this->user->getRole();
			
		//Set the user to active
		$this->active= true;
		
		//Add the active property and user id to the session
		$this->Session->set_userdata(array("active" => $this->active, "u" => $this->user->getId()));
		
	}
	
	/**
	 * Wrapper method for destroying the session essentially 
	 * logging out the current user
	 * 
	 * @author David Mans
	 */
	public function LogOut()
	{
		//Unsetting the user and their role
		unset($this->user);
		unset($this->userRole);
		
		//Deactivate the user
		$this->active = false;
		
		//Destroy the session
		$this->Session->destroy();
	}
	
	/**
	 * Verifies whether the provided user is the user that is on
	 * record in the database. This helps prevent against session
	 * hijacking where a users information has been modified.
	 * 
	 * @param User $user
	 * @return boolean
	 */
	public function ValidateUser(User $user)
	{
		//Build a user query
		$query = new UserQuery();
		
		//Fetch the user based on the provided users id
		$newUser = $query->findById($user->getId());
		
		//Compare the objects and return if they match or not
		if($user === $newUser)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Validates whether or not an email is a standard email address.  
	 * 
	 * @author David Mans
	 * @param string $email
	 * @return boolean
	 */
	protected function emailValidation($email)
	{
		//Verify if the email fits the standard pattern
		if(!preg_match('/[a-zA-Z0-9_.+-]+@([a-zA-Z0-9_])+?(\.[a-zA-Z]{2,6})+/', $email))
		{
			return false;
		} 
		else 
		{
			return true;
		}
	}
}

?>