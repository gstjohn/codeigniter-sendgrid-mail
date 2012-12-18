# CodeIgniter-SendGrid-Mail

CodeIgniter-SendGrid-Mail is a CodeIgniter wrapper for the [SendGrid Mail API](http://docs.sendgrid.com/documentation/api/web-api/mail/).

## Requirements

1. PHP 5.1+
2. CodeIgniter 2.0.0+
3. cURL
4. CodeIgniter REST Client Library: [http://getsparks.org/packages/restclient/show](http://getsparks.org/packages/restclient/show)

## Included Methods

**Initialization**

1. `initialize()` - Set up the library with API credentials and settings

**Mail**

1. `send()` - Send an email

**Errors**

1. `error_message()` - Get error message

## Usage

	// Load the SendGrid spark
	$this->load->spark('sendgrid-mail/0.1.2');

	// Initialize (not necessary if set in config)
	$this->sendgrid_mail->initialize(array('api_user'   => 'my_username',
	                                	   'api_key'    => 'secret_key',
	                                	   'api_format' => 'json'));

	// Send email
	$result = $this->sendgrid_mail->send('john@doe.com', 'Welcome to Oz!', 'You may see the wizard now.', NULL, 'mail@yourdomain.com');
