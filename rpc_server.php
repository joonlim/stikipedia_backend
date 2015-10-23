<?php 
	/**
	 * This API is used to create a connection between a RPC server and a
	 * RabbitMQ message broker using PHP.
	 *
	 * Important:
	 * RabbitMQ must be set up in the producer, consumers, and brokers.
	 * 
	 * Usage: 
	 * 1. Create an instance of RpcServer in the consumer.
	 * The constructor takes file (default: "broker_ip.txt") which should 
	 * contain the message broker's IP on the first line.
	 * 2. Call RpcServer::start(), which takes a binding key an a function to
	 * call when a message is received by the producer. Calling start(), will 
	 * cause this script to run until it is turned off.
	 * 3. The binding key must match the routing key of the producer for the
	 * message to be recognized.
	 * 5. The function should take a string as its only parameter since it will
	 * be handling the producer's message.
	 */
	
	// include libraries
	require_once __DIR__ . '/vendor/autoload.php';
	use PhpAmqpLib\Connection\AMQPStreamConnection;
	use PhpAmqpLib\Message\AMQPMessage;

	$func;
	$key;

	/**
	 * A class used to receive messages to a message broker.
	 */
	class RpcServer {

		private $connection;
	    private $channel;	    
        private $broker_ip;

        public function getBrokerIP() {
            return $this->broker_ip;
        }

	    public function call($str) {
			echo " [.] Received: $str\n";

	    	global $func;
			return $func($str);
		}

	    private function createAMQPStreamConnection($file) {

			// get the broker ip from a file "broker_ip.txt"
			$myfile = fopen($file, "r") or die("Unable to open file!");
			$filestring = fread($myfile,filesize($file));

			$lines = explode("\n", $filestring);
			$broker_ip = $lines[0];

			// create a connection to the server
			$connection = new AMQPStreamConnection($broker_ip, 5672, 'guest', 'guest');
			return $connection;
		}

	    public function __construct($file = "broker_ip.txt") {
	    	// create a connection to the server
	        $this->connection = $this->createAMQPStreamConnection($file);
	        $this->channel = $this->connection->channel();

	    }

	    public function start($binding_key, $function) {

	    	global $func, $key;
	    	$key = $binding_key;
	    	$func = $function;

	    	// define a PHP callable that will receive the messages sent by the server.
			$callback = function($req) {
				global $key;

			    $str = $req->body;

			    $response = $this->call($str);

			   	echo " [x] Forwarded: $response\n";

			    // message to be returned to client
			    $msg = new AMQPMessage(
			        (string) $response,
			        array('correlation_id' => $req->get('correlation_id'))
			        );

			    $req->delivery_info['channel']->basic_publish(
			        $msg, '', $req->get('reply_to'));
			    $req->delivery_info['channel']->basic_ack(
			        $req->delivery_info['delivery_tag']);
			};

	    	$this->channel->queue_declare($binding_key, false, false, false, false);

	    	echo " [x] Awaiting RPC requests\n";

			// don't dispatch a new message to a worker until it has processed previous one
			$this->channel->basic_qos(null, 1, null);
			$this->channel->basic_consume($binding_key, '', false, false, false, false, $callback);

			// keep this program running.
			while(count($this->channel->callbacks)) {
			    $this->channel->wait();
			}

			// close channel and connection
			$this->channel->close();
			$this->connection->close();
	    }

	}

?>
