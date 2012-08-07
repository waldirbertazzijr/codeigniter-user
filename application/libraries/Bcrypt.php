<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Bcrypt Class
 *
 * @package		Orion Project
 * @subpackage	Libraries
 * @category	Crypt
 * @author		Waldir Bertazzi Junior
 * @link		http://waldir.org/
 */

class Bcrypt {
	private $times;
	private $random_state;
	
	function __construct($times = 12){
		$this->CI =& get_instance();
		
		// Bcrypt not supported
		if(CRYPT_BLOWFISH != 1) {
			show_error('Bcrypt is not installed or is not supported in this system. You may try the following:<ul><li>Install bcrypt support (http://php.net/crypt).</li><li>Change the encrypt method on codeigniter-user config file under config/user.php');
		}

		$this->times = $times;
	}
	
	function hash($input){
		$hash = crypt($input, $this->generate_salt());
		
		if(strlen($hash) > 13) return $hash;
		return false;
	}
	
	/**
	 * Função que valida o password recebido com o hash do banco.
	 *
	 * @return booleano
	 * @author Waldir Bertazzi Junior
	 **/
	public function compare($input, $hash_existente) {
		$hash = crypt($input, $hash_existente);
		return $hash === $hash_existente;
	}
	
	function generate_salt(){
		$salt = sprintf('$2a$%02d$', $this->times);
		
		// generate random bytes for our salt
		$bytes = $this->get_random_bytes(16);
		
		$salt .= $this->encode_bytes($bytes);
		
		return $salt;
	}
	
	/**
	 * Funcao que retorna bytes aleatorios de diferentes fontes
	 *
	 * @return random bytes
	 * @param numero de bytes pra gerar
	 * @author Waldir Bertazzi Junior
	 **/
	function get_random_bytes($count){
		$bytes = '';
		
		if(function_exists('openssl_random_pseudo_bytes') && !(PHP_OS == 'Windows' || PHP_OS == 'WIN32' || PHP_OS == 'WINNT')) {
			$bytes = openssl_random_pseudo_bytes($count);
		}
		
		if($bytes === '' && is_readable('/dev/urandom') && ($h_rand = @fopen('/dev/urandom', 'rb')) !== false){
			$bytes = fread($h_rand, $count);
			fclose($h_rand);
		}
		
		if(strlen($bytes) < $count) {
			$bytes = '';
			
			if($this->random_state === null) {
				$this->random_state = microtime();
				if(function_exists('getmypid')) {
					$this->random_state .= getmypid();
				}
			}
		
		
			for($i = 0; $i < $count; $i += 16) {
				$this->random_state = md5(microtime() . $this->random_state);
				if (PHP_VERSION >= '5') {
					$bytes .= md5($this->random_state, true);
				} else {
					$bytes .= pack('H*', md5($this->random_state));
				}
			}

			$bytes = substr($bytes, 0, $count);
		}
		
	    return $bytes;
	}
	
	/**
	 * Pedaco de codigo retirado da biblioteca PHP Password Hashing Framework
	 *
	 * @return void
	 * @author PHP Password Hashing Framework
	 * @link http://www.openwall.com/phpass/
	 **/
	function encode_bytes($input){
		$itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

	    $output = '';
	    $i = 0;
		do {
			$c1 = ord($input[$i++]);
			$output .= $itoa64[$c1 >> 2];
			$c1 = ($c1 & 0x03) << 4;
			if ($i >= 16) {
				$output .= $itoa64[$c1];
				break;
			}

			$c2 = ord($input[$i++]);
			$c1 |= $c2 >> 4;
			$output .= $itoa64[$c1];
			$c1 = ($c2 & 0x0f) << 2;

			$c2 = ord($input[$i++]);
			$c1 |= $c2 >> 6;
			$output .= $itoa64[$c1];
			$output .= $itoa64[$c2 & 0x3f];
		} while (1);

	    return $output;
	}
	
}
?>
