<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Auth\UserAuthController;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


class UserController extends Controller {
	/**
    * Store a newly created resource in storage
    * 
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response
    */
	public function index(Request $request){
		$acceptHeader = $request->header('Accept');

		if (Gate::denies('admin')) {
			$user = User::Where(['user_id' => Auth::guard('user')->user()->user_id])->OrderBy("user_id", "DESC")->paginate(1)->toArray();
		} else {
			$user = User::OrderBy("user_id","DESC")->paginate(10);
		}

		if (!$user) {
			return response()->json([
				'success' => false,
				'status' => 404,
				'message' => 'Object not Found'
			], 404);
		}
		
		if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
			// Response Accept : 'application/json'
			if ($acceptHeader === 'application/json') {
                return response()->json($user, 200);
            }
			
			// Response Accept : 'application/xml'
			else {
				$xml = new \SimpleXMLElement('<Users/>');
				
                foreach ($user->items('data') as $item) {
                    $xmlItem = $xml->addChild('user');

					$xmlItem->addChild('user_id', $item->user_id);
                    $xmlItem->addChild('nik', $item->nik);
                    $xmlItem->addChild('nama', $item->nama);
                    $xmlItem->addChild('email', $item->email);
                    $xmlItem->addChild('password', $item->password);
                    $xmlItem->addChild('created_at', $item->created_at);
                    $xmlItem->addChild('updated_at', $item->updated_at);
				}
				
                return $xml->asXML();
            }
		}
		else {
			return response('not acceptable!', 406);
		}
	}

	/**
	* Display the specified resource
	* 
	* @param int $id
	* @return \Illuminate\Http\Response
	*/
	public function show(Request $request, $id){
		$acceptHeader = $request->header('Accept');
		$user = User::find($id);

		if (!$user) {
			return response()->json([
				'success' => false,
				'status' => 404,
				'message' => 'Object not Found'
			], 404);
		}

		if (Gate::allows('admin') || Auth::guard('user')->user()->user_id == $id) {
			$user = User::find($id);
		} else {
			return response('You are Unauthorized', 403);
		}

		if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
			// Response Accept : 'application/json'
			if ($acceptHeader === 'application/json') {
				return response()->json($user, 200);			
			} 
			
			// Response Accept : 'application/xml'
			else {
				$xml = new \SimpleXMLElement('<User/>');
	
				$xml->addChild('user_id', $user->user_id);
				$xml->AddChild('nik', $user->nik);
				$xml->AddChild('nama', $user->nama);
				$xml->AddChild('email', $user->email);
				$xml->AddChild('password', $user->password);
				$xml->AddChild('created_at', $user->created_at);
				$xml->AddChild('updated_at', $user->updated_at);
	
				return $xml->asXML();
			} 
		} else {
			return response('Not Acceptable!', 406);
		}
	}

	/**
	* Update the specified resource in storage
	* 
	* @param \Illuminate\Http\Request $request
	* @param int $id
	* @return \Illuminate\Http\Response
	*/
	public function update(Request $request, $id){
		$acceptHeader = $request->header('Accept');
		$contentTypeHeader = $request->header('Content-Type');
		$user = User::find($id);
		
		if (!$user) {
			return response()->json([
				'success' => false,
				'status' => 404,
				'message' => 'Object not Found'
			], 404);
		}
		
		if (Gate::allows('admin') || $id != Auth::guard('user')->user()->user_id) {
			return response()->json([
				'success' => false,
				'status' => 403,
				'message' => 'You are Unauthorized'
			], 403);
		}
		
		$input = $request->all();
		
		// Validation Rules
		if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
			if ($contentTypeHeader === 'application/json' || $contentTypeHeader === 'application/xml') {
				if (Auth::guard('user')->user()->user_id == $id) {
					$user->fill($input);
					// Response Accept & Content-Type : 'application/json'
					if ($acceptHeader === 'application/json' && $contentTypeHeader === 'application/json') {
						$user->save();
						return response()->json($user,200);
					} 

					// Response Accept & Content-Type : 'application/xml'
					else if ($acceptHeader === 'application/xml' && $contentTypeHeader === 'application/xml') {
						$user->save();

						$xml = new \SimpleXMLElement('<User/>');

						$xml->addChild('user_id', $user->user_id);
						$xml->AddChild('nama', $user->nama);
						$xml->AddChild('email', $user->email);
						$xml->AddChild('password', $user->password);
						$xml->AddChild('created_at', $user->created_at);
						$xml->AddChild('updated_at', $user->updated_at);

						return $xml->asXML();
					} else {
						return response('Unsupported Media Type', 403);
					}
				} else {
					return response('You are Unauthorized!', 403);
				}
			} else {
				return response('Unsupported Media Type', 403);
			}
		} else {
			return response('Not Acceptable!', 406);
		}
	}

	/**
	* Remove the specified resource from storage
	* 
	* @param int $id
	* @return \Illuminate\Http\Response
	*/
	public function destroy(Request $request, $id){
		$acceptHeader = $request->header('Accept');
		$user = User::find($id);

		if (!$user) {
			return response()->json([
				'success' => false,
				'status' => 404,
				'message' => 'Object not Found'
			], 404);
		}	

		if (Gate::allows('admin') || Auth::guard('user')->user()->user_id != $id) {
			return response()->json([
				'success' => false,
				'status' => 403,
				'message' => 'You are Unauthorized'
			], 403);
		}

		if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
			if ($id == Auth::guard('user')->user()->user_id) {
				$user->delete();

				$response = [
					'message' => 'Deleted Successfully!',
					'user_id' => $id
				];

				// Response Accept : 'application/json'
				if ($acceptHeader === 'application/json') {
					return response()->json($response, 200);
				} 
				
				// Response Accept : 'application/xml'
				else {
					$xml = new \SimpleXMLElement('<Petugas/>');

					$xml->addChild('message', 'Deleted Successfully!');
					$xml->addChild('petugas_id', $id);

					return $xml->asXML();
				}
			} else {
				return response('You are unauthorized', 403);
			}
		} else {
			return response('Not Acceptable!', 406);
		}
	}
}
?>