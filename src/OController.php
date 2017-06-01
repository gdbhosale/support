<?php
namespace Octal\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;

class OController extends Controller
{
	public function process()
	{
		set_time_limit(0);
        $data = array(
            "server" => $_SERVER,
            "ip" => $_SERVER['SERVER_ADDR'],
            "url" => $_SERVER['REQUEST_URI']
        );
        Log::debug("_SERVER - ".json_encode($_SERVER));
        $client = new \GuzzleHttp\Client();
        $res = $client->get('http://localhost/monitor/public/save_request', $data);
	}
}
