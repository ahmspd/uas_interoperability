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
        // Authorization
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
            if (Auth::user()->role === 'super admin') {
                $petugas = Petugas::OrderBy("petugas_id", "DESC")->first()->paginate(2)->toArray();
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
                $petugas = Petugas::Where(['petugas_id' => Auth::user()->petugas_id])->OrderBy("petugas_id", "DESC")->paginate(1)->toArray();
                return response()->json($petugas, 200);
            }
        } else {
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
        
        $acceptHeader = $request->header('Accept');

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {

            if (Auth::user()->role === 'super admin') {
                $petugas = Petugas::find($id)->OrderBy("petugas_id", "DESC")->paginate(1)->toArray();

                if (!$petugas) {
                    abort(404);
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

                    foreach ($petugas['data'] as $item) {
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
                if ($id == Auth::user()->petugas_id) {
                    $petugas = Petugas::find($id)->OrderBy("petugas_id", "DESC")->paginate(1)->toArray();

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

                        foreach ($petugas['data'] as $item) {
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
                    return response('You are Unauthorized', 403);
                }
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

        $input = $request->all();

        $petugas = Petugas::find($id);
        
        if (!$petugas) {
            abort(404);
        }

        $petugas->fill($input);
        $petugas->save();

        return response()->json($petugas, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id) {
        $acceptHeader = $request->header('Accept');
        $contentTypeHeader = $request->header('Content-Type');
        
        $petugas = Petugas::find($id);

        if (!$petugas) {
            abort(404);
        }

        $petugas->delete();
        $response = [
            'message' => 'Deleted Successfully!',
            'petugas_id' => $id
        ];

        return response()->json($response, 200);
    }
}
?>