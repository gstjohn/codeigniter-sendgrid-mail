<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * SendGrid Mail Library for CodeIgniter
 *
 * Wrapper for working with the SendGrid Mail API
 *
 * @package CodeIgniter
 * @version 0.1.0
 * @author Bold
 * @link http://hellobold.com
 */
class Sendgrid_Mail
{

	protected $api_endpoint  = 'https://sendgrid.com/api/';
	protected $error_message = '';
	protected $api_user      = '';
	protected $api_key       = '';
	protected $api_format    = 'json';
	protected $ci;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param array params Initialization parameters
	 */
	public function __construct($params = array())
	{
		$this->ci =& get_instance();

		// load sparks
		$this->ci->load->spark('restclient/2.0.0');

		// load config vars
		$this->ci->load->config('sendgrid');
		if ( ! isset($params['api_user']))   { $this->api_user   = config_item('api_user'); }
		if ( ! isset($params['api_key']))    { $this->api_key    = config_item('api_key'); }
		if ( ! isset($params['api_format'])) { $this->api_format = config_item('api_format'); }

		// initialize parameters
		$this->initialize($params);

		log_message('debug', 'SendGrid Mail Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize settings
	 *
	 * @access public
	 * @param array $params Settings parameters
	 */
	public function initialize($params = array())
	{
		if (is_array($params) && ! empty($params))
		{
			foreach($params as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}
		}

		// set format to json if an invalid format was provided
		if ($this->api_format != 'xml' && $this->api_format != 'json')
		{
			$this->api_format = 'json';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Send a mail
	 *
	 * @access public
	 * @param string $to To address(es). Can be an array to send to multiple locations.
	 * @param string $subject Email subject
	 * @param string $text Text version of email. Required if $html is left empty.
	 * @param string $html HTML version of email. Requred if $text is left empty.
	 * @param string $from From email address from your domain.
	 * @param string $toname Recipient names. Must be an array of equal length if array provided to $to. (optional)
	 * @param string $xsmtpapi JSON headers (optional).
	 * @param string $bcc Email address(es) to blind cc. Can be an array to send to multiple locations (optional).
	 * @param string $fromname Name appended to $from email field (optional).
	 * @param string $replyto Email address used for replies from recipient (optional).
	 * @param string $date RFC 2822 formatted date string to use in email header (optional).
	 * @param string $files An array of file names with full paths to be attached to the email (optional). Must be less than 7MB total.
	 * @param string $headers An array of key/value pairs in JSON format to be placed into the header (optional).
	 * @return bool
	 */
	public function send($to, $subject, $text=NULL, $html=NULL, $from, $toname=NULL, $xsmtpapi=NULL, $bcc=NULL, $fromname=NULL, $replyto=NULL, $date=NULL, $files=NULL, $headers=array())
	{
		// input validation
		if (is_null($text) && is_null($html))
		{
			$this->error_message = "At minimum, either \$text or \$html must be provided.";
		}

		// add required data
		$email_data = array(
			'to'      => $to,
			'subject' => $subject,
			'from'    => $from
		);

		// add optional data
		if ( ! is_null($text))     { $email_data['text']      = $text; }
		if ( ! is_null($html))     { $email_data['html']      = $html; }
		if ( ! is_null($toname))   { $email_data['toname']    = $toname; }
		if ( ! is_null($xsmtpapi)) { $email_data['x-smtpapi'] = $xsmtpapi; }
		if ( ! is_null($bcc))      { $email_data['bcc']       = $bcc; }
		if ( ! is_null($fromname)) { $email_data['fromname']  = $fromname; }
		if ( ! is_null($replyto))  { $email_data['replyto']   = $replyto; }
		if ( ! is_null($date))     { $email_data['date']      = $date; }
		if ( ! is_null($files))    { $email_data['files']     = $files; }
		if (count($headers) > 0)   { $email_data['headers']   = $headers; }

		return $this->_send('mail.send.' . $this->api_format, $email_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Get error message
	 *
	 * @access public
	 * @return string
	 */
	public function error_message()
	{
		return $this->error_message;
	}

	// --------------------------------------------------------------------

	/**
	 * Send the request to SendGrid
	 *
	 * @access private
	 * @param string $url The portion of the URL after the API endpoint
	 * @param array $data The data to be sent along with the request (optional)
	 * @return mixed
	 */
	private function _send($url, $data = array())
	{
		// set credentials
		$creds = array(
			'api_user' => $this->api_user,
			'api_key'  => $this->api_key
		);

		// initialize rest library
		$this->ci->rest->initialize(array('server' => $this->api_endpoint));
		$this->ci->rest->format($this->api_format);

		// merge credentials into data
		$data = array_merge($creds, $data);

		// post request
		$response = $this->ci->rest->post($url, $data);

		// check for 5xx response codes
        if (substr($this->ci->rest->status(), 0, 1) == 5)
        {
            $this->error_message = 'Access to SendGrid failed. Please try again later.';
            return FALSE;
        }
		// check for an error message response
		elseif (isset($response->errors) && count($response->errors) > 0)
		{
			$this->error_message = $response->errors[0] . '.';
			return FALSE;
		}
		// check for a success message response
		elseif (isset($response->message) && $response->message == 'success')
		{
			return TRUE;
		}

		// return the response data
		return $response;
	}

}
