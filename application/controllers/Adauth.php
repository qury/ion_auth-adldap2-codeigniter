<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Load the adldap2 library via composer
include_once (FCPATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

class Adauth extends CI_Controller
{

	/**
	 * Theme css for adauth pages
	 *
	 * @var string
	 */
	public $theme;

	/**
	 * Table to store extra user information
	 *
	 * @var string
	 */
	public $user_extra;

	/**
	 *  User table
	 * @var string
	 */
	public $users;

	/**
	 * Groups table
	 *
	 * @var string
	 */
	public $groups;

	/**
	 * User group table
	 *
	 * @var string
	 */
	public $users_groups;

	/**
	 * Login attempts table
	 *
	 * @var string
	 */
	public $login_attempts;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->database();
		$this->load->library(array('ion_auth', 'form_validation'));
		$this->load->helper(array('url', 'language'));
		$this->load->config('adauth');
		$this->load->config('ion_auth');

		$this->theme			 = $this->config->item('bootstrap_theme', 'adauth');
		$this->users			 = $this->config->item('users', 'tables');
		$this->groups			 = $this->config->item('groups', 'tables');
		$this->users_groups		 = $this->config->item('users_groups', 'tables');
		$this->login_attempts	 = $this->config->item('login_attempts', 'tables');
		$this->user_extra		 = $this->config->item('user_extra', 'tables');

		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('adauth');
	}

	// redirect if needed, otherwise display the user list
	public function index()
	{

		if (!$this->ion_auth->logged_in())
		{
			// redirect them to the login page
			redirect('adauth/login', 'refresh');
		}
		elseif (!$this->ion_auth->is_admin()) // remove this elseif if you want to enable this for non-admins
		{
			// redirect them to the home page because they must be an administrator to view this
			$this->session->flashdata('You must be an administrator to view this page.');
			redirect($this->config->item('unauthorized', 'adauth'));
		}
		else
		{
			// set the flash data error message if there is one
			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

			//list the users
			$this->data['users'] = $this->ion_auth->users()->result();
			foreach ($this->data['users'] as $k => $user)
			{
				$this->data['users'][$k]->groups = $this->ion_auth->get_users_groups($user->id)->result();
			}

			$this->render_view('adauth/index', $this->data);
		}
	}

	/**
	 * Login user with AD
	 */
	public function login()
	{

		// Check whether the user is already logged in, if so redirect to the success page.
		if ($this->ion_auth->logged_in())
		{
			$this->session->set_flashdata('message', 'You are already logged in!');
			redirect($this->config->item('success', 'adauth'), 'refresh');
		}

		// Set login form data
		$this->data['title'] = $this->lang->line('login_heading');
		$this->data['theme'] = $this->theme;


		// Form validation rules
		$this->form_validation->set_rules('identity', str_replace(':', '', $this->lang->line('login_identity_label')), 'trim|required');
		$this->form_validation->set_rules('password', str_replace(':', '', $this->lang->line('login_password_label')), 'trim|required');
		$this->form_validation->set_rules('domain', str_replace(':', '', $this->lang->line('login_domain_label')), 'trim|required');

		// Validate login form
		if ($this->form_validation->run() == true)
		{
			// Form is submitted succesfully so we are beginning the login process
			// Try login with local then with adldap2 login to make the experience seamless
			$remember = (bool) $this->input->post('remember');

			// Register hook to get the values of the
			$user = ['username' => $this->input->post('identity')];
			$this->ion_auth->set_hook('pre_set_session', 'pre_set_session', $this, 'pre_set_session', array($user));

			// Local login
			if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember))
			{
				log_message('info', PHP_EOL . 'Succesful local login' . PHP_EOL);
				//if the login is successful
				//redirect them back to the home page
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				redirect('/', 'refresh');
			}
			else
			{
				log_message('info', PHP_EOL . 'trying  adauth login' . PHP_EOL);
				// Login using adldap2 auth
				$user = $this->ad_auth($this->input->post('identity'), $this->input->post('password'), $this->input->post('domain'));

				if ($user != FALSE)
				{
					log_message('info', PHP_EOL . 'Succesful adauth login' . PHP_EOL);

					// Succesful login
					// Check wheter it is a new user or an existing one.
					if ($this->_exists(strtolower($user['username'])))
					{
						log_message('info', PHP_EOL . 'Succesful adauth login -- Existing User' . PHP_EOL);
						// User exists so we will use a dummy password to ion_auth
						$dummy_password	 = random_bytes(30);
						$pre_data		 = [
							'dummy_password' => $dummy_password, 'username'		 => $user['username'],
						];

						// Execute the pre_login hook where we update the password to match the dummy one.
						$this->ion_auth->set_hook('pre_login', 'pre_login', $this, 'pre_login', array($pre_data));

						//Update the existing user?
						if ($this->config->item('update_existing_user', 'adauth'))
						{
							$this->ion_auth->set_hook('extra_where', 'extra_where', $this, 'update_existing_user', array($user));
						}


						// Before setting the session details we will update the the details of
						$this->ion_auth->set_hook('pre_set_session', 'pre_set_session', $this, 'pre_set_session', array($user));

						// Login the user the above hooks will run where required.
						$this->ion_auth->login($user['username'], $dummy_password);
					}
					else
					{
						log_message('info', PHP_EOL . 'Succesful adauth login -- New User' . PHP_EOL);
						// New user
						// We will have to register the user and update the informations in the standard and extra tables
						//
					// The below hook will run after the user is registered and add set the extra attributes.
						$this->ion_auth->set_hook('post_register', 'post_register', $this, 'post_reg', array($user));

						// Register  new user
						$this->ion_auth->register($user['username'], 'dummy_password', $user['email']);


						// This hook runs after
						$this->ion_auth->set_hook('pre_set_session', 'pre_set_session', $this, 'pre_set_session', array($user));

						// log in the user
						$this->ion_auth->login($user['username'], 'dummy_password');
					}


					$this->session->set_flashdata('message', $this->ion_auth->messages());

					redirect($this->config->item('success', 'adauth'), 'refresh');
				}
				else
				{
					// if the login was un-successful
					// redirect them back to the login page
					$this->session->set_flashdata('message', 'Login failed!');
					redirect('adauth/login', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
				}
			}
		}
		else
		{
			// the user is not logging in so display the login page
			// set the flash data error message if there is one
			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

			$this->data['identity']	 = array('name'	 => 'identity',
				'id'	 => 'identity',
				'type'	 => 'text',
				'value'	 => $this->form_validation->set_value('identity'),
			);
			$this->data['password']	 = array('name'	 => 'password',
				'id'	 => 'password',
				'type'	 => 'password',
			);
			$this->data['domain']	 = [
				'name'	 => 'domain',
				'id'	 => 'domain',
				'type'	 => 'input',
			];

			// Domain values
			foreach (array_keys($this->config->item('domain')) as $dm)
			{
				$this->data['domain_values'][$dm] = $dm;
			}

			$this->load->view('adauth/common/header', $this->data);
			$this->load->view('adauth/login', $this->data);
			$this->load->view('adauth/common/footer');
		}
	}

	// log the user out
	public function logout()
	{
		$this->data['title'] = "Logout";

		// log the user out
		$logout = $this->ion_auth->logout();

		// redirect them to the login page
		$this->session->set_flashdata('message', $this->ion_auth->messages());
		redirect($this->config->item('success', 'adauth'), 'refresh');
	}

	/**
	 * Check whether the user exists
	 *
	 * @param string $user
	 * @return boolean true/false
	 *
	 */
	private function _exists($user)
	{

		$tables = $this->config->item('tables', 'ion_auth');

		$this->db->select('username');

		switch ($this->db->platform())
		{

			case'postgre':
				$this->db->where("lower(username)", strtolower($user));
				break;
			default:
				$this->db->where('username', $user);
				break;
		}

		$sql = $this->db->get_compiled_select($tables['users']);

		$q		 = $this->db->query($sql)->result();
		$count	 = count($q);

		if ($count > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	public function unauthorized()
	{
		$data = [
			'session'	 => $_SESSION,
			'user'		 => [
				$this->ion_auth->user()->row(),
				$this->ion_auth->get_users_groups()->result(),
			],
			'theme'		 => $this->theme,
		];
		$this->load->view('adauth/common/header', $data);
		$this->load->view('adauth/unauthorized', $data);
		$this->load->view('adauth/common/footer');
	}

	public function _get_csrf_nonce()
	{
		$this->load->helper('string');
		$key	 = random_string('alnum', 8);
		$value	 = random_string('alnum', 20);
		$this->session->set_flashdata('csrfkey', $key);
		$this->session->set_flashdata('csrfvalue', $value);

		return array($key => $value);
	}

	public function _valid_csrf_nonce()
	{
		$csrfkey = $this->input->post($this->session->flashdata('csrfkey'));
		if ($csrfkey && $csrfkey == $this->session->flashdata('csrfvalue'))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/*	 * ************************************************************* */

	/**
	 *  AD auth method
	 *
	 * @param string $user Username
	 * @param string $password  Password
	 * @param string $domain the desired domain
	 * @return boolean or array
	 */
	public function ad_auth($user, $password, $domain)
	{

		$ad = new \Adldap\Adldap();

		// Create a configuration array.
		$url = $this->config->item($domain, 'domain');
		$dc	 = 'dc=' . str_replace('.', ',dc=', $url);

		$config = [
			'domain_controllers' => [$url],
			'base_dn'			 => $dc,
			'admin_username'	 => $domain . '\\' . $user,
			'admin_password'	 => $password,
		];

		// Add a connection provider to Adldap.

		$ad->addProvider($config);

		try
		{
			$provider = $ad->connect();

			// Authentincating
			if ($provider->auth()->attempt($domain . '\\' . $user, $password))
			{

				// Performing a query.
				$search	 = $provider->search();
				$record	 = $search->findBy('samaccountname', $user);

				$ret = [
					'username'		 => strtolower($user),
					'display_name'	 => $record->getDisplayName(),
					'first_name'	 => $record->getLastName(),
					'last_name'		 => $record->getFirstName(),
					'phone'			 => $record->getTelephoneNumber(),
					'company'		 => $record->getCompany(),
					'email'			 => $record->getEmail(),
					'domain'		 => $domain,
				];

				return $ret;
			}
		}
		catch (\Adldap\Auth\BindException $e)
		{
			// The user didn't supply a username.
			log_message('error', 'BindException: ' . print_r($e->getMessage(), true));
			return FALSE;
		}
		catch (\Adldap\Auth\UsernameRequiredException $e)
		{
			// The user didn't supply a username.
			log_message('error', 'UsernameRequiredException: ' . print_r($e->getMessage(), true));
			return FALSE;
		}
		catch (\Adldap\Auth\PasswordRequiredException $e)
		{
			// The user didn't supply a password.
			log_message('error', 'PasswordRequiredException: ' . print_r($e->getMessage(), true));
			return FALSE;
		}
	}

	private function render_view($view, $data = NULL)
	{
		$data['theme'] = $this->theme;


		$this->load->view('adauth/common/header', $data);
		$this->load->view('common/nav');
		$this->load->view($view, $data);
		$this->load->view('adauth/common/footer');
	}

	public function pre_login($password)
	{

		$tables = $this->config->item('tables', 'ion_auth');

		$data = [
			'password' => $this->ion_auth->hash_password($password['dummy_password']),
		];

		$this->db
			->where('lower(username)', strtolower($password['username']))
			->update($tables['users'], $data);
	}

	/**
	 * This method gets called if we need to update the user with the AD values.
	 *
	 * @param array $user
	 */
	public function update_existing_user($user)
	{
		$tables = $this->config->item('tables', 'ion_auth');

		$data = [
			'first_name' => $user['first_name'],
			'last_name'	 => $user['last_name'],
			'company'	 => $user['company'],
			'email'		 => $user['email'],
			'phone'		 => $user['phone'],
		];

		$q = $this->db
			->where('lower(username)', strtolower($user['username']))
			->update($tables['users'], $data);
	}

	/**
	 * This function will be executed on successful login
	 * The main function of it will be to add in the extra data into the session
	 *
	 */
	public function pre_set_session($user)
	{

		// get the table value:

		$tables = $this->config->item('tables', 'ion_auth');

		// Get the user id of the freshly created user
		$query = $this->db
			->select('id')
			->where('username', strtolower($user['username']))
			->from($tables['users'])
			->get_compiled_select($tables['users']);



		$query = $this->db->query($query)->result();


		$id = $query[0]->id;


		$this->config->load('adauth');
		$tablesAdauth = $this->config->item('user_extra', 'tables');


		$d = $this->db
			->select([
				$tablesAdauth . '.domain',
				$tablesAdauth . '.theme',
				$tablesAdauth . '.navbar',
			])
			->where($tablesAdauth . '.id', $id)
			->get($tablesAdauth)
			->result();

		// Check whether the user has this data, if not use the values from the
		// configuration file.
		$this->config->load('adauth');

		if (isset($d[0]->id) && $d[0]->id > 0)
		{
			$data['domain']	 = $d[0]->domain;
			$data['theme']	 = $d[0]->theme;
			$data['navbar']	 = $d[0]->navbar;
		}
		else
		{
			$data['domain']	 = $this->config->item('default_domain', 'adauth');
			$data['theme']	 = $this->config->item('bootstrap_theme', 'adauth');
			$data['navbar']	 = $this->config->item('bootstrap_navbar', 'adauth');
		}
		// Set the above variables to the session
		$this->session->set_userdata($data);
	}

	public function edit_user($id)
	{
		$this->data['title'] = $this->lang->line('edit_user_heading');

		if (!$this->ion_auth->logged_in() || (!$this->ion_auth->is_admin() && !($this->ion_auth->user()->row()->id == $id)))
		{
			redirect('adauth', 'refresh');
		}

		$user			 = $this->ion_auth->user($id)->row();
		$groups			 = $this->ion_auth->groups()->result_array();
		$currentGroups	 = $this->ion_auth->get_users_groups($id)->result();

		// Get extra user information
		$tablesAdauth = $this->config->item('user_extra', 'tables');

		$extraData = $this->db->select('*')->where('id', $id)->get($tablesAdauth)->result_object();

		if (!isset($extraData[0]) || empty($extraData[0]))
		{
			$extraData[0]			 = new stdClass();
			$extraData[0]->domain	 = $this->config->item('default_domain', 'adauth');
			$extraData[0]->theme	 = $this->config->item('bootstrap_theme', 'adauth');
			$extraData[0]->navbar	 = $this->config->item('bootstrap_navbar', 'adauth');
		}
		$this->data['user_id'] = (int) $id;


		// validate form input
		$this->form_validation->set_rules('first_name', $this->lang->line('edit_user_validation_fname_label'), 'trim|required');
		$this->form_validation->set_rules('last_name', $this->lang->line('edit_user_validation_lname_label'), 'trim|required');
		//$this->form_validation->set_rules('phone', $this->lang->line('edit_user_validation_phone_label'), 'required');
		//$this->form_validation->set_rules('company', $this->lang->line('edit_user_validation_company_label'), 'required');
		//
		// Check whether we are dealing with a submitter for or not.
		if (isset($_POST) && !empty($_POST))
		{
			// do we have a valid request?
			if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id'))
			{
				show_error($this->lang->line('error_csrf'));
			}

			// update the password if it was posted
			if ($this->input->post('password'))
			{
				$this->form_validation->set_rules('password', $this->lang->line('edit_user_validation_password_label'), 'trim|required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
				$this->form_validation->set_rules('password_confirm', $this->lang->line('edit_user_validation_password_confirm_label'), 'trim|required');
			}

			if ($this->form_validation->run() === TRUE)
			{
				$data = array(
					'first_name' => $this->input->post('first_name'),
					'last_name'	 => $this->input->post('last_name'),
					'company'	 => $this->input->post('company'),
					'phone'		 => $this->input->post('phone'),
				);

				// Extra values
				$data_extra = [
					'id'	 => $id, // user id from GET
					'domain' => $this->input->post('domain'),
					'theme'	 => $this->input->post('theme'),
					'navbar' => $this->input->post('navbar'),
				];

				// Register method to update the extra data
				$this->ion_auth->set_hook('post_update_user_successful', 'post_update_user_successful', $this, 'post_update_user_successful', array($data_extra));


				// update the password if it was posted
				if ($this->input->post('password'))
				{
					$data['password'] = $this->input->post('password');
				}



				// Only allow updating groups if user is admin
				if ($this->ion_auth->is_admin())
				{
					//Update the groups user belongs to
					$groupData = $this->input->post('groups');

					if (isset($groupData) && !empty($groupData))
					{

						$this->ion_auth->remove_from_group('', $id);

						foreach ($groupData as $grp)
						{
							$this->ion_auth->add_to_group($grp, $id);
						}
					}
				}

				// check to see if we are updating the user
				if ($this->ion_auth->update($user->id, $data))
				{
					// redirect them back to the admin page if admin, or to the base url if non admin
					$this->session->set_flashdata('message', $this->ion_auth->messages());
					if ($this->ion_auth->is_admin())
					{
						redirect('adauth', 'refresh');
					}
					else
					{
						redirect('adauth/edit_user/' . $id, 'refresh');
					}
				}
				else
				{
					// redirect them back to the admin page if admin, or to the base url if non admin
					$this->session->set_flashdata('message', $this->ion_auth->errors());
					if ($this->ion_auth->is_admin())
					{
						redirect('adauth', 'refresh');
					}
					else
					{
						redirect('adauth/edit_user/' . $id, 'refresh');
					}
				}
			}
		}

		// display the edit user form
		$this->data['csrf'] = $this->_get_csrf_nonce();

		// set the flash data error message if there is one
		$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

		// pass the user to the view
		$this->data['user']			 = $user;
		$this->data['groups']		 = $groups;
		$this->data['currentGroups'] = $currentGroups;
		$this->data['extraData']	 = $extraData; // extra user data result object

		$this->data['first_name']		 = array(
			'name'	 => 'first_name',
			'id'	 => 'first_name',
			'type'	 => 'text',
			'value'	 => $this->form_validation->set_value('first_name', $user->first_name),
		);
		$this->data['last_name']		 = array(
			'name'	 => 'last_name',
			'id'	 => 'last_name',
			'type'	 => 'text',
			'value'	 => $this->form_validation->set_value('last_name', $user->last_name),
		);
		$this->data['company']			 = array(
			'name'	 => 'company',
			'id'	 => 'company',
			'type'	 => 'text',
			'value'	 => $this->form_validation->set_value('company', $user->company),
		);
		$this->data['phone']			 = array(
			'name'	 => 'phone',
			'id'	 => 'phone',
			'type'	 => 'text',
			'value'	 => $this->form_validation->set_value('phone', $user->phone),
		);
		$this->data['password']			 = array(
			'name'	 => 'password',
			'id'	 => 'password',
			'type'	 => 'password'
		);
		$this->data['password_confirm']	 = array(
			'name'	 => 'password_confirm',
			'id'	 => 'password_confirm',
			'type'	 => 'password'
		);

		// Domain values
		foreach (array_keys($this->config->item('domain')) as $dm)
		{
			$this->data['domain_values'][$dm] = $dm;
		}

		$this->data['domain'] = [
			'name'	 => 'domain',
			'id'	 => 'domain',
			'type'	 => 'input',
			'value'	 => $this->form_validation->set_value('domain', $extraData[0]->domain),
			'list'	 => $this->data['domain_values']
		];

		// Theme values
		foreach (array_keys($this->config->item('theme')) as $th)
		{
			$this->data['theme_values'][$th] = $th;
		}
		asort($this->data['theme_values']);

		$this->data['themes'] = [
			'name'	 => 'theme',
			'id'	 => 'theme',
			'value'	 => $this->form_validation->set_value('domain', $extraData[0]->theme),
			'list'	 => $this->data['theme_values'],
		];

		// Navbar details
		$this->data['navbar'] = [
			'name'	 => 'navbar',
			'id'	 => 'navbar',
			'value'	 => $this->form_validation->set_value('navbar', $extraData[0]->navbar),
			'list'	 => [
				'default'	 => 'default',
				'inverse'	 => 'inverse'
			],
		];

		$this->render_view('adauth/edit_user', $this->data);
	}

	// create a new user
	public function create_user()
	{
		$this->data['title'] = $this->lang->line('create_user_heading');

		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			redirect('adauth', 'refresh');
		}

		$tables							 = $this->config->item('tables', 'ion_auth');
		$identity_column				 = $this->config->item('identity', 'ion_auth');
		$this->data['identity_column']	 = $identity_column;

		// validate form input
		$this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'required');
		$this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'required');
		if ($identity_column !== 'email')
		{
			$this->form_validation->set_rules('identity', $this->lang->line('create_user_validation_identity_label'), 'required|is_unique[' . $tables['users'] . '.' . $identity_column . ']');
			// $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email');
		}
		else
		{
			// $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email|is_unique[' . $tables['users'] . '.email]');
		}
		$this->form_validation->set_rules('phone', $this->lang->line('create_user_validation_phone_label'), 'trim');
		$this->form_validation->set_rules('company', $this->lang->line('create_user_validation_company_label'), 'trim');
		$this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
		$this->form_validation->set_rules('password_confirm', $this->lang->line('create_user_validation_password_confirm_label'), 'required');

		if ($this->form_validation->run() == true)
		{
			$email		 = strtolower($this->input->post('email'));
			$identity	 = ($identity_column === 'email') ? $email : $this->input->post('identity');
			$password	 = $this->input->post('password');

			$additional_data = array(
				'first_name' => $this->input->post('first_name'),
				'last_name'	 => $this->input->post('last_name'),
				'company'	 => $this->input->post('company'),
				'phone'		 => $this->input->post('phone'),
			);

			$extraData = [
				'domain'	 => $this->input->post('domain'),
				'theme'		 => $this->input->post('theme'),
				'navbar'	 => $this->input->post('navbar'),
				'username'	 => $identity,
				'manual'	 => 1
			];

			// The below hook will run after the user is registered and add set the extra attributes.
			$this->ion_auth->set_hook('post_register', 'post_register', $this, 'post_reg', array($extraData));
		}
		if ($this->form_validation->run() == true && $this->ion_auth->register($identity, $password, $email, $additional_data))
		{
			// check to see if we are creating the user
			// redirect them back to the admin page
			$this->session->set_flashdata('message', $this->ion_auth->messages());

			redirect("adauth", 'refresh');
		}
		else
		{
			// display the create user form
			// set the flash data error message if there is one
			$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

			$this->data['first_name']	 = array(
				'name'	 => 'first_name',
				'id'	 => 'first_name',
				'type'	 => 'text',
				'value'	 => $this->form_validation->set_value('first_name'),
			);
			$this->data['last_name']	 = array(
				'name'	 => 'last_name',
				'id'	 => 'last_name',
				'type'	 => 'text',
				'value'	 => $this->form_validation->set_value('last_name'),
			);
			$this->data['identity']		 = array(
				'name'	 => 'identity',
				'id'	 => 'identity',
				'type'	 => 'text',
				'value'	 => $this->form_validation->set_value('identity'),
			);
			$this->data['email']		 = array(
				'name'	 => 'email',
				'id'	 => 'email',
				'type'	 => 'text',
				'value'	 => $this->form_validation->set_value('email'),
			);

			$defaultCompany = $_POST['company'] ?? $this->config->item('default_company', 'adauth');

			$this->data['company']			 = array(
				'name'	 => 'company',
				'id'	 => 'company',
				'type'	 => 'text',
				'value'	 => $this->form_validation->set_value('company', $defaultCompany),
			);
			$this->data['phone']			 = array(
				'name'	 => 'phone',
				'id'	 => 'phone',
				'type'	 => 'text',
				'value'	 => $this->form_validation->set_value('phone'),
			);
			$this->data['password']			 = array(
				'name'	 => 'password',
				'id'	 => 'password',
				'type'	 => 'password',
				'value'	 => $this->form_validation->set_value('password'),
			);
			$this->data['password_confirm']	 = array(
				'name'	 => 'password_confirm',
				'id'	 => 'password_confirm',
				'type'	 => 'password',
				'value'	 => $this->form_validation->set_value('password_confirm'),
			);
			// Domain values
			foreach (array_keys($this->config->item('domain')) as $dm)
			{
				$this->data['domain_values'][$dm] = $dm;
			}

			$defaultDomain = $_POST['domain'] ?? $this->config->item('default_domain', 'adauth');

			$this->data['domain'] = [
				'name'	 => 'domain',
				'id'	 => 'domain',
				'type'	 => 'input',
				'value'	 => $this->form_validation->set_value('domain', $defaultDomain),
				'list'	 => $this->data['domain_values']
			];

			// Theme values
			foreach (array_keys($this->config->item('theme')) as $th)
			{
				$this->data['theme_values'][$th] = $th;
			}
			asort($this->data['theme_values']);

			$defaultTheme = $_POST['theme'] ?? $this->config->item('bootstrap_theme', 'adauth');

			$this->data['themes'] = [
				'name'	 => 'theme',
				'id'	 => 'theme',
				'value'	 => $this->form_validation->set_value('theme', $defaultTheme),
				'list'	 => $this->data['theme_values'],
			];

			$defaultNavbar = $_POST['navbar'] ?? $this->config->item('bootstrap_navbar', 'adauth');

			// Navbar details
			$this->data['navbar'] = [
				'name'	 => 'navbar',
				'id'	 => 'navbar',
				'value'	 => $this->form_validation->set_value('navbar', $defaultNavbar),
				'list'	 => [
					'default'	 => 'default',
					'inverse'	 => 'inverse'
				],
			];

			$this->render_view('adauth/create_user', $this->data);
		}
	}

	public function delete_user($id)
	{
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			// redirect them to the home page because they must be an administrator to view this
			return show_error('You must be an administrator to view this page.');
		}

		if ($id === NULL)
		{
			redirect('adauth', 'refresh');
		}

		$id = (int) $id;


		$tables = $this->config->item('tables', 'ion_auth');

		// Delete from groups
		$this->db->where('user_id', $id);
		$this->db->delete($tables['groups']);



		// Delete extra prefx
		$this->db->where('id', $id);
		$this->db->delete($this->config->item('user_extra', 'tables'));

		// Delete user itself
		$this->db->where('id', $id);
		$this->db->delete($tables['users']);

		redirect('adauth', 'refresh');
	}

	// activate the user
	public function activate($id, $code = false)
	{
		if ($code !== false)
		{
			$activation = $this->ion_auth->activate($id, $code);
		}
		else if ($this->ion_auth->is_admin())
		{
			$activation = $this->ion_auth->activate($id);
		}

		if ($activation)
		{
			// redirect them to the auth page
			$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect("adauth", 'refresh');
		}
		else
		{
			// redirect them to the forgot password page
			$this->session->set_flashdata('message', $this->ion_auth->errors());
			redirect("adauth", 'refresh');
		}
	}

// deactivate the user
	public function deactivate($id = NULL)
	{
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			// redirect them to the home page because they must be an administrator to view this
			return show_error('You must be an administrator to view this page.');
		}

		$id = (int) $id;

		$this->load->library('form_validation');
		$this->form_validation->set_rules('confirm', $this->lang->line('deactivate_validation_confirm_label'), 'trim|required');
		$this->form_validation->set_rules('id', $this->lang->line('deactivate_validation_user_id_label'), 'trim|required|alpha_numeric');

		if ($this->form_validation->run() == FALSE)
		{
			// insert csrf check
			$this->data['csrf']	 = $this->_get_csrf_nonce();
			$this->data['user']	 = $this->ion_auth->user($id)->row();

			$this->render_view('adauth/deactivate_user', $this->data);
		}
		else
		{
			// do we really want to deactivate?
			if ($this->input->post('confirm') == 'yes')
			{
				// do we have a valid request?
				if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id'))
				{
					show_error($this->lang->line('error_csrf'));
				}

				// do we have the right userlevel?
				if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin())
				{
					$this->ion_auth->deactivate($id);
				}
			}

			// redirect them back to the auth page
			redirect('adauth', 'refresh');
		}
	}

	// create a new group
	public function create_group()
	{
		$this->data['title'] = $this->lang->line('create_group_title');

		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			redirect('adauth', 'refresh');
		}

		// validate form input
		$this->form_validation->set_rules('group_name', $this->lang->line('create_group_validation_name_label'), 'required|trim|alpha_dash');

		if ($this->form_validation->run() == TRUE)
		{
			$new_group_id = $this->ion_auth->create_group(strtolower($this->input->post('group_name')), $this->input->post('description'));
			if ($new_group_id)
			{
				// check to see if we are creating the group
				// redirect them back to the admin page
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				redirect("adauth", 'refresh');
			}
		}
		else
		{
			// display the create group form
			// set the flash data error message if there is one
			$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

			$this->data['group_name']	 = array(
				'name'	 => 'group_name',
				'id'	 => 'group_name',
				'type'	 => 'text',
				'value'	 => $this->form_validation->set_value('group_name'),
			);
			$this->data['description']	 = array(
				'name'	 => 'description',
				'id'	 => 'description',
				'type'	 => 'text',
				'value'	 => $this->form_validation->set_value('description'),
			);

			$this->render_view('adauth/create_group', $this->data);
		}
	}

	public function edit_group($id)
	{

		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			// redirect them to the home page because they must be an administrator to view this
			return show_error('You must be an administrator to view this page.');
		}

		$id = (int) $id;


		// bail if no group id given
		if (!$id || empty($id))
		{
			redirect('adauth', 'refresh');
		}

		$this->data['title'] = $this->lang->line('edit_group_title');

		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			redirect('adauth', 'refresh');
		}

		$group = $this->ion_auth->group($id)->row();

		// validate form input
		$this->form_validation->set_rules('group_name', $this->lang->line('edit_group_validation_name_label'), 'required|alpha_dash');

		if (isset($_POST) && !empty($_POST))
		{
			if ($this->form_validation->run() === TRUE)
			{
				$group_update = $this->ion_auth->update_group($id, $_POST['group_name'], $_POST['group_description']);

				if ($group_update)
				{
					$this->session->set_flashdata('message', $this->lang->line('edit_group_saved'));
				}
				else
				{
					$this->session->set_flashdata('message', $this->ion_auth->errors());
				}
				redirect("adauth", 'refresh');
			}
		}

		// set the flash data error message if there is one
		$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

		// pass the user to the view
		$this->data['group'] = $group;

		$readonly = $this->config->item('admin_group', 'ion_auth') === $group->name ? 'readonly' : '';

		$this->data['group_name']		 = array(
			'name'		 => 'group_name',
			'id'		 => 'group_name',
			'type'		 => 'text',
			'value'		 => $this->form_validation->set_value('group_name', $group->name),
			$readonly	 => $readonly,
		);
		$this->data['group_description'] = array(
			'name'	 => 'group_description',
			'id'	 => 'group_description',
			'type'	 => 'text',
			'value'	 => $this->form_validation->set_value('group_description', $group->description),
		);

		$this->render_view('adauth/edit_group', $this->data);
	}

	public function delete_group($id)
	{

		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			// redirect them to the home page because they must be an administrator to view this
			return show_error('You must be an administrator to view this page.');
		}

		$id = (int) $id;

		// bail if no group id given
		$restricted = [1, 2]; //these groups can't be deleted
		if (!$id || empty($id) || in_array($id, $restricted))
		{
			redirect('adauth', 'refresh');
		}

		$tables = $this->config->item('tables', 'ion_auth');

		// Deleting the user association first
		$q = $this->db
			->where('group_id', $id)
			->delete($tables['users_groups']);

		// Deleting the group itself
		$q = $this->db
			->where('id', $id)
			->delete($tables['groups']);

		redirect('adauth/list_groups', 'refresh');
	}

	function list_groups()
	{
		$tables = $this->config->item('tables', 'ion_auth');

		//select fields and join tables
		$this->db->select('*');
		$this->db->order_by('id', 'ASC');
		$this->db->from($tables['groups']);

		$this->data['members'] = $this->db->get()->result_array();

		$this->render_view('adauth/list_groups', $this->data);
	}

	/**
	 * List of Group Members
	 * @param type $group_id
	 */
	function group_members($group_id)
	{
		// bail if no group id given
		if (!$group_id || empty($group_id))
		{
			redirect('adauth', 'refresh');
		}

		$tables = $this->config->item('tables', 'ion_auth');

		//select fields and join tables
		$this->data['group'] = $this->db
				->select('*')
				->where('id', $group_id)
				->from($tables['groups'])
				->get()->result_array();

		$this->data['group_id'] = $group_id;

		//select fields and join tables
		$this->data['members'] = $this->db
				->select('*')
				->where('group_id', $group_id)
				->order_by('user_id', 'ASC')
				->from($tables['users_groups'])
				->join($tables['users'], '' . $tables['users'] . '.id = ' . $tables['groups'] . '.user_id')
				->get()->result_array();

		$this->render_view('adauth/group_members', $this->data);
	}

	public function remove_group_member($user_id, $group_id)
	{
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			// redirect them to the home page because they must be an administrator to view this
			return show_error('You must be an administrator to view this page.');
		}
		$tables = $this->config->item('tables', 'ion_auth');

		// bail if no group id given
		$restricted = [1]; // administrator should not be removed using this.
		if (!$user_id || empty($user_id) || empty($group_id) || in_array($user_id, $restricted))
		{
			redirect('adauth', 'refresh');
		}
		$user_id	 = (int) $user_id;
		$group_id	 = (int) $group_id;

		// Deleting the user association first
		$q = $this->db
			->where('group_id', $group_id)
			->where('user_id', $user_id)
			->delete($table['users_groups']);

		redirect('adauth/group_members/' . $group_id, 'refresh');
	}

	/**
	 * Post registration function updates the extra table and the existing fields on the user table
	 *
	 * @param array $user['email','firt_name','last_name','company','phone']
	 */
	public function post_reg($user)
	{

		$tables = $this->config->item('tables', 'ion_auth');

		// Get the user id of the freshly created user
		$id = $this->db
				->select('id')
				->where('username', strtolower($user['username']))
				->get($tables['users'])->result();

		$data = [
			'domain' => $user['domain'],
			'theme'	 => $this->config->item('bootstrap_theme', 'adauth'),
			'navbar' => $this->config->item('bootstrap_navbar', 'adauth'),
			'id'	 => $id[0]->id,
		];

		$this->db->insert($this->config->item('user_extra', 'tables'), $data);

		// Update existing user with informaiton from the domain
		// This is only triggered if there is an array key 'manual'
		if (!array_key_exists('manual', $user))
		{
			$update = [
				'email'		 => $user['email'],
				'first_name' => $user['first_name'],
				'last_name'	 => $user['last_name'],
				'company'	 => $user['company'],
				'phone'		 => $user['phone'],
			];

			$this->db->where('id', $id[0]->id)->update($tables['users'], $update);
		}
	}

	/**
	 * This function is intended to be used to update the user extra data
	 * when we use user_edit($id)
	 * @param array $data  [id,domain,theme,navbar]
	 */
	public function post_update_user_successful($data)
	{

		$this->db->where('id', (int) $data['id']);
		$u = $this->db->count_all_results($this->user_extra);


		if (0 === $u)
		{
			//Insert
			$q = $this->db
				->insert($this->config->item('user_extra', 'tables'), $data);
		}
		else
		{
			//update
			$q = $this->db
				->where('id', (int) $data['id'])
				->update($this->config->item('user_extra', 'tables'), $data);
		}

		/* $q = $this->db
		  ->where('id', (int) $data['id'])
		  ->update($this->user_extra, $data); */

		if ($q)
		{
			// update was succesful let's update the session if the current
			// user ID matches the id from the data array
			$id = $this->ion_auth->user()->row()->id;
			if ((int) $id == (int) $data['id'])
			{
				$this->session->set_userdata([
					'navbar' => $data['navbar'],
					'theme'	 => $data['theme'],
					'domain' => $data['domain']
				]);
			}
		}
		else
		{
			$message = PHP_EOL
				. 'post_update_user_successful was unsuccesful'
				. PHP_EOL
				. print_r($data, true)
				. PHP_EOL;

			log_message('error', $message);
		}
	}

}
