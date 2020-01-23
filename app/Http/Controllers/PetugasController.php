<?php
    namespace App\Http\Controllers;

    use App\Models\Petugas;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Gate;
class PetugasController extends Controller {
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
     public function index(Request $request) {
        if (Gate::denies('admin')) {
            return response()->json([
                'success' => false,
                'status' => 403,
                'message' => 'You are Unauthorized'
            ], 403);
        }

        $acceptHeader = $request->header('Accept');
        
        // Validating Header : 'Accept'
        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            if (Auth::guard('admin')->user()->role === 'super admin') {
                $petugas = Petugas::OrderBy("petugas_id", "DESC")->first()->paginate(10)->toArray();

                if (!$petugas) {
                    return response()->json([
                        'success' => false,
                        'status' => 404,
                        'message' => 'Object not Found'
                    ], 404);
                }

                $response = [
                    "total_count" => $petugas["total"],
                    "limit" => $petugas["per_page"],
                    "pagination" => [
                        "next_page" => $petugas["next_page_url"],
                        "current_page" => $petugas["current_page"]
                    ], 
                    "data" => $petugas["data"]
                ];
                // Response Accept : 'application/json'
                if ($acceptHeader === 'application/json') {
                    return response()->json($response, 200);
                } 

                // Response Accept : 'application/xml'
                else {
                    $xml = new \SimpleXMLElement('<DataPetugas/>');
                    $xml->addChild('total_count', $petugas['total']);
                    $xml->addChild('limit', $petugas['per_page']);
                    $pagination = $xml->addChild('pagination');
                    $pagination->addChild('next_page', $petugas['next_page_url']);
                    $pagination->addChild('current_page', $petugas['current_page']);
                    $xml->addChild('total_count', $petugas['total']);

                    foreach($petugas['data'] as $item) {
                        $xmlItem = $xml->addChild('petugas');

                        $xmlItem->addChild('petugas_id', $item['petugas_id']);
                        $xmlItem->addChild('role', $item['role']);
                        $xmlItem->addChild('email', $item['email']);
                        $xmlItem->addChild('created_at', $item['created_at']);
                        $xmlItem->addChild('updated_at', $item['updated_at']);
                    }                

                    return $xml->asXML();
                }
            } else {
                $petugas = Petugas::Where(['petugas_id' => Auth::guard('admin')->user()->petugas_id])->OrderBy("petugas_id", "DESC")->paginate(1)->toArray();

                if (!$petugas) {
                    return response()->json([
                        'success' => false,
                        'status' => 404,
                        'message' => 'Object not Found'
                    ], 404);
                }

                // Response Accept : 'application/json'
                if ($acceptHeader === 'application/json') {
                    return response()->json($petugas, 200);
                } 
                // Response Accept : 'application/xml
                else {
                    $xml = new \SimpleXMLElement('<Petugas/>');

                    foreach ($petugas['data'] as $item) {
                        $xml->addChild('petugas_id', $item['petugas_id']);
                        $xml->addChild('role', $item['role']);
                        $xml->addChild('email', $item['email']);
                        $xml->addChild('created_at', $item['created_at']);
                        $xml->addChild('updated_at', $item['updated_at']);
                    }

                    return $xml->asXML();
                }
            }
        } 
        else {
            return response('Not Acceptable!', 406);
        }
     }

    /**
     * Display the specified resource.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) {
        if (Gate::denies('admin')) {
            return response()->json([
                'success' => false,
                'status' => 403,
                'message' => 'You are Unauthorized'
            ], 403);
        }
        
        if (Auth::guard('admin')->user()->role === 'super admin' || Auth::guard('admin')->user()->petugas_id == $id) {
            $petugas = Petugas::find($id);
        }

        if (!$petugas) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Object not Found'
            ], 404);
        }

        $acceptHeader = $request->header('Accept');

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            // Response Accept : 'application/json'
            if ($acceptHeader === 'application/json') {
                return response()->json($petugas, 200);
            }

            // Response Accept : 'application/xml'
            else {
                $xml = new \SimpleXMLElement('<Petugas/>');
                    
                $xml->addChild('petugas_id', $petugas->petugas_id);
                $xml->addChild('role', $petugas->role);
                $xml->addChild('email', $petugas->email);
                $xml->addChild('created_at', $petugas->created_at);
                $xml->addChild('updated_at', $petugas->updated_at);

                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable!', 406);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $acceptHeader = $request->header('Accept');
        $contentTypeHeader = $request->header('Content-Type');
        $petugas = Petugas::find($id);

        if (!$petugas) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Object not Found'
            ], 404);
        }

        if (Gate::denies('admin')) {
            return response()->json([
                'success' => false,
                'status' => 403,
                'message' => 'You are Unauthorized'
            ], 403);
        }
    
        $input = $request->all();

        // Validation Rules
        $validationRules = [
            'email' => 'required|min:5|unique:petugas',
            'password' => 'required|min:6',
            'role' => 'required|in:super admin,admin',
            'user_id' => $id
        ];

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            if (Auth::guard('admin')->user()->role === 'super admin' || $id == Auth::guard('admin')->user()->petugas_id) {
                $petugas->fill($input);
                $petugas->save();

                if ($contentTypeHeader === 'application/json' && $acceptHeader === 'application/json') {
                    return response()->json($petugas, 200);
                } else if ($contentTypeHeader === 'application/xml' && $acceptHeader === 'application/xml') {
                    $xml = new \SimpleXMLElement('<Petugas/>');

                    $xml->addChild('petugas_id', $petugas->petugas_id);
                    $xml->addChild('role', $petugas->role);
                    $xml->addChild('email', $petugas->email);
                    $xml->addChild('created_at', $petugas->created_at);
                    $xml->addChild('updated_at', $petugas->updated_at);

                    return $xml->asXML();
                } else {
                    return response('Unsupported Media Type', 403);
                }
            } else {
                return response('You are Unauthorized', 403);
            }
        } else {
            return response('Not Acceptable!', 406);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id) {
        $acceptHeader = $request->header('Accept');
        $petugas = Petugas::find($id);

        if (!$petugas) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Object not Found'
            ], 404);
        }
        
        if (Gate::denies('admin')) {
            return response()->json([
                'success' => false,
                'status' => 403,
                'message' => 'You are Unauthorized'
            ], 403);
        }
        
        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            if (Auth::guard('admin')->user()->role === 'super admin' || $id == Auth::guard('admin')->user()->petugas_id) {
                $petugas->delete();
                $response = [
                    'message' => 'Deleted Successfully!',
                    'petugas_id' => $id
                ];

                if ($acceptHeader === 'application/json') {
                    return response()->json($response, 200);
                } else {
                    $xml = new \SimpleXMLElement('<Petugas/>');

                    $xml->addChild('message', 'Deleted Successfully!');
                    $xml->addChild('petugas_id', $id);
                }
            } else {
                return response('You are Unauthorized', 403);
            }
        } else {
            return response('Not Acceptable!', 406);
        }
    }
}
?>