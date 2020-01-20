<?php
namespace App\Http\Controllers;

use App\Models\Saran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SaranController extends Controller {
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $acceptHeader = $request->header('Accept');

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $saran = Saran::OrderBy("user_id", "DESC")->paginate(10)->toArray();

            if (!$saran) {
                return response()->json([
                    'success' => false,
                    'status' => 404,
                    'message' => 'Object not Found'
                ], 404);
            }

            $response = [
                "total_count" => $saran["total"],
                "limit" => $saran["per_page"],
                "pagination" => [
                    "next_page" => $saran["next_page_url"],
                    "current_page" => $saran["current_page"]
                ],
                "data" => $saran["data"],
            ];

            // Response Accept : 'application/json'
            if ($acceptHeader === 'application/json') {
                return response()->json($response, 200);
            }

            // Response Accept : 'application/xml'
            else {
                $xml = new \SimpleXMLElement('<Data_Saran/>');

                $xml->addChild('total_count', $saran['total']);
                $xml->addChild('limit', $saran['per_page']);
                $pagination = $xml->addChild('pagination');
                $pagination->addChild('next_page', $saran['next_page_url']);
                $pagination->addChild('current_page', $saran['current_page']);
                $xml->addChild('total_count', $saran['total']);

                foreach ($saran['data'] as $item) {
                    $xmlItem = $xml->addChild('saran');

                    $xmlItem->addChild('saran_id', $item['saran_id']);
                    $xmlItem->addChild('user_id', $item['user_id']);
                    $xmlItem->addChild('jenis_saran', $item['jenis_saran']);
                    $xmlItem->addChild('lokasi_saran', $item['lokasi_saran']);
                    $xmlItem->addChild('isi_saran', $item['isi_saran']);
                    $xmlItem->addChild('created_at', $item['created_at']);
                    $xmlItem->addChild('updated_at', $item['updated_at']);
                }

                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable!', 406);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $acceptHeader = $request->header('Accept');
        $contentTypeHeader = $request->header('Content-Type');

        if (Gate::allows('admin')) {
            return response()->json([
                'success' => false,
                'status' => 403,
                'message' => 'You are Unauthorized'
            ], 403);
        }

        $input = $request->all();

        $validationRules = [
            'jenis_saran' => 'required|in:pelayanan,infrastruktur',
            'lokasi_saran' => 'required',
            'isi_saran' => 'required',
        ];

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $saran = new Saran;
        $saran->user_id = Auth::guard('user')->user()->user_id;
        $saran->jenis_saran = $request->input('jenis_saran');
        $saran->lokasi_saran = $request->input('lokasi_saran');
        $saran->isi_saran = $request->input('isi_saran');
        
        if ($acceptHeader === 'application/json' || $contentTypeHeader === 'application/xml') {
            if ($contentTypeHeader === 'application/json' || $contentTypeHeader === 'application/xml') {
                if ($acceptHeader === 'application/json' && $contentTypeHeader === 'application/json') {
                    $saran->save();

                    return response()->json($saran, 200);
                }
                else if ($acceptHeader === 'appication/xml' && $contentTypeHeader === 'application/xml'){
                    $saran->save();

                    $xml = new \SimpleXMLElement('<saran/>');

                    $xml->addChild('saran_id', $saran->keluhan_id);
                    $xml->addChild('user_id', $saran->user_id);
                    $xml->addChild('jenis_saran', $saran->jenis_saran);
                    $xml->addChild('lokasi_saran', $saran->lokasi_saran);
                    $xml->addChild('isi_saran', $saran->isi_saran);
                    $xml->addChild('created_at', $saran->created_at);
                    $xml->addChild('updated_at', $saran->updated_at);

                    return $xml->asXML();
                }
                else {
                    return response('Not Acceptable!', 406);
                }
            } else {
                return response('Unsupported Media Type', 403);
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
        $acceptHeader = $request->header('Accept');

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $saran = Saran::find($id);

            if (!$saran) {
                return response()->json([
                    'success' => false,
                    'status' => 404,
                    'message' => 'Object not Found'
                ], 404);
            }

            // Response Accept : 'application/json'
            if ($acceptHeader === 'application/json') {
                return response()->json($saran, 200);
            }

            // Response Accept : 'application/xml'
            else {
                $xml = new \SimpleXMLElement('<saran/>');

                $xml->addChild('saran_id', $saran->keluhan_id);
                $xml->addChild('user_id', $saran->user_id);
                $xml->addChild('jenis_saran', $saran->jenis_saran);
                $xml->addChild('lokasi_saran', $saran->lokasi_saran);
                $xml->addChild('isi_saran', $saran->isi_saran);
                $xml->addChild('created_at', $saran->created_at);
                $xml->addChild('updated_at', $saran->updated_at);

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
        $saran = Saran::find($id);

        if (Gate::allows('admin') || $saran->user_id != Auth::guard('user')->user()->user_id) {
            return response()->json([
                'success' => false,
                'status' => 403,
                'message' => 'You are Unauthorized'
            ], 403);
        }

        $input = $request->all();

        $validationRules = [
            'jenis_saran' => 'required|in:pelayanan,infrastruktur',
            'lokasi_saran' => 'required',
            'isi_saran' => 'required',
        ];

        $validator = Validator::make($input, 
            $validationRules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $acceptHeader = $request->header('Accept');
        $contentTypeHeader = $request->header('Content-Type');
        
        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            if (!$saran) {
                return response()->json([
                    'success' => false,
                    'status' => 404,
                    'message' => 'Object not Found'
                ], 404);
            }
            $saran->fill($input);
            $saran->save();

            if ($acceptHeader === 'application/json') {
                if ($contentTypeHeader === 'application/json') {
                    return response()->json($saran, 200);
                } else {
                    return response('Unsupported Media Type', 403);
                }
            }
            else if ($acceptHeader === 'application/xml') {
                if ($contentTypeHeader === 'application/xml') {
                    $xml = new \SimpleXMLElement('<saran/>');

                    $xml->addChild('saran_id', $saran->keluhan_id);
                    $xml->addChild('user_id', $saran->user_id);
                    $xml->addChild('jenis_saran', $saran->jenis_saran);
                    $xml->addChild('lokasi_saran', $saran->lokasi_saran);
                    $xml->addChild('isi_saran', $saran->isi_saran);
                    $xml->addChild('created_at', $saran->created_at);
                    $xml->addChild('updated_at', $saran->updated_at);

                    return $xml->asXML();
                }
                else {
                    return response('Unsupported Media Type', 403);
                }
            }
            else {
                return response('Not Acceptable!', 406);
            }
        }
        else {
            return response('Not Acceptable!', 406);
        }
        // Lanjutkan function update nya son
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
	public function destroy(Request $request, $id){
        $acceptHeader = $request->header('Accept');
        $saran = Saran::find($id);

        if (Gate::allows('admin') || $saran->user_id != Auth::guard('user')->user()->user_id) {
            return response()->json([
                'success' => false,
                'status' => 403,
                'message' => 'You are Unauthorized'
            ], 403);
        }
        
        if (!$saran) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Object not Found'
            ], 404);
        }
        
        // Validating Header : 'Accept'
        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $saran->delete();
                
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
                $xml = new \SimpleXMLElement('<saran/>');

                $xml->addChild('message', 'Deleted Successfully!');
                $xml->addChild('petugas_id', $id);

                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable!', 406);
        }
	}
}
?>