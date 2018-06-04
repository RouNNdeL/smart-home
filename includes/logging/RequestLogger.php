<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-06-04
 * Time: 16:22
 */

require_once __DIR__ . "/../database/DbUtils.php";

class RequestLogger
{
    private $id;

    private static $instance = null;

    /**
     * RequestLogger constructor.
     * @param $id
     */
    private function __construct($id)
    {
        $this->id = $id;
        register_shutdown_function(array(&$this, "updateHttpCodeAuto"));
    }

    /**
     * @param SessionManager $sessionManager
     * @return RequestLogger
     */
    public static function getInstance(SessionManager $sessionManager)
    {
        if(RequestLogger::$instance === null)
        {
            RequestLogger::$instance = RequestLogger::create(
                $sessionManager->getSessionId(),
                $_SERVER["SCRIPT_NAME"],
                $_SERVER["REQUEST_URI"],
                $_SERVER["REQUEST_METHOD"],
                $_SERVER["REMOTE_ADDR"]
            );
        }
        return RequestLogger::$instance;
    }

    /**
     * @param int $session_id
     * @param string $resource
     * @param string $uri
     * @param string $method
     * @param string $ip
     * @return RequestLogger
     */
    private static function create(int $session_id, string $resource, string $uri, string $method, string $ip)
    {
        $conn = DbUtils::getConnection();
        $sql = "INSERT INTO log_request (session_id, ip, uri, method, resource) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $session_id, $ip, $uri, $method, $resource);
        $stmt->execute();
        $stmt->close();

        return new RequestLogger($conn->insert_id);
    }

    public function updateHttpCodeAuto()
    {
        $this->updateHttpCode(http_response_code());
    }

    private function updateHttpCode(int $code)
    {
        $conn = DbUtils::getConnection();
        $sql = "UPDATE log_request SET http_response = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $code, $this->id);
        $stmt->execute();
        $stmt->close();
    }
}