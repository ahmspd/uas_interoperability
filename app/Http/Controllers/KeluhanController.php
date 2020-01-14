<?php
namespace App\Http\Controllers;

use App\Models\Keluhan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class KeluhanController extends Controller {
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $acceptHeader = $request->header('Accept');

        if (Gate::allows('admin')) {
            $keluhan = Keluhan::OrderBy("keluhan_id", "DESC")->paginate(10);
        } else {
            $keluhan = Keluhan::Where("user_id", Auth::guard('user')->user()->user_id);
        }

        if (!$keluhan) {
            abort(404);
        }

         if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $response = [
                "total_count" => $keluhan["total"],
                "limit" => $keluhan["per_page"],
                "pagination" => [
                    "next_page" => $keluhan["next_page_url"],
                    "current_page" => $keluhan["current_page"]
                ],
                "data" => $keluhan["data"],
            ];
            
            // Response Accept : 'application/json'
            if ($acceptHeader === 'application/json') {
                return response()->json($response, 200);
            }
            // Response Accept : 'application/xml'
            else {
                $xml = new \SimpleXMLElement('<Data_Keluhan/>');

                $xml->addChild('total_count', $keluhan['total']);
                $xml->addChild('limit', $keluhan['per_page']);
                $pagination = $xml->addChild('pagination');
                $pagination->addChild('next_page', $keluhan['next_page_url']);
                $pagination->addChild('current_page', $keluhan['current_page']);
                $xml->addChild('total_count', $keluhan['total']);

                foreach ($keluhan['data'] as $item) {
                    $xmlItem = $xml->addChild('keluhan');

                    $xmlItem->addChild('keluhan_id', $item['keluhan_id']);
                    $xmlItem->addChild('user_id', $item['user_id']);
                    $xmlItem->addChild('jenis_keluhan', $item['jenis_keluhan']);
                    $xmlItem->addChild('lokasi_keluhan', $item['lokasi_keluhan']);
                    $xmlItem->addChild('foto_keluhan', $item['foto_keluhan']);
                    $xmlItem->addChild('isi_keluhan', $item['isi_keluhan']);
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

        if (Gate::denies('admin')) {
            return response()->json([
                'success' => false,
                'status' => 403,
                'message' => 'You are Unauthorized'
            ], 403);
        }

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $input = $request->all();

            $validationRules = [
                'jenis_keluhan' => 'required|in:pelayanan,infrastruktur',
                'lokasi_keluhan' => 'required',
                'foto_keluhan' => 'required',
                'isi_keluhan' => 'required',
            ];

            $validator = Validator::make($input, $validationRules);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $keluhan = new Keluhan;
            $keluhan->user_id = Auth::guard('user')->user()->user_id;
            $keluhan->jenis_keluhan = $request->input('jenis_keluhan');
            $keluhan->lokasi_keluhan = $request->input('lokasi_keluhan');

            if ($request->hasFile('foto_keluhan')) {
                $imgName = 'foto_keluhan2';
                $request->file('foto_keluhan')->move(storage_path('uploads/foto_keluhan'),$imgName);
                $keluhan->foto_keluhan = $imgName;
            }
            
            $keluhan->isi_keluhan = $request->input('isi_keluhan');
            $keluhan->save();
            return response()->json($keluhan, 200);
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

        if (Gate::allows('admin')) {
            $keluhan = Keluhan::find($id);
        } else {
            $keluhan = Keluhan::find(Auth::guard('user')->user()->keluhan_id);
        }

        if (!$keluhan) {
            abort(404);
        }

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            // Response Accept : 'application/json'
            if ($acceptHeader === 'application/json') {
                return response()->json($keluhan, 200);
            } 
            
            // Response Accept : 'application/xml'
            else {
                $xml = new \SimpleXMLElement('<Keluhan/>');

                $xml->addChild('keluhan_id', $keluhan->keluhan_id);
                $xml->addChild('user_id', $keluhan->user_id);
                $xml->addChild('jenis_keluhan', $keluhan->jenis_keluhan);
                $xml->addChild('lokasi_keluhan', $keluhan->lokasi_keluhan);
                $xml->addChild('foto_keluhan', $keluhan->foto_keluhan);
                $xml->addChild('isi_keluhan', $keluhan->isi_keluhan);
                $xml->addChild('created_at', $keluhan->created_at);
                $xml->addChild('updated_at', $keluhan->updated_at);

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
        
        if (Gate::allows('admin')) {
            $keluhan = Keluhan::find($id);
        } else {
            $keluhan = Keluhan::find(Auth::guard('user')->user()->keluhan_id);
        }

        if (!$keluhan) {
            abort(404);
        }
        
        // Lanjutkan nak....
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id) {
        $acceptHeader = $request->header('Accept');

        if (Gate::allows('admin')) {
            $keluhan = Keluhan::find($id);
        } else {
            $keluhan = Keluhan::find(Auth::guard('user')->user()->keluhan_id);
        }

        if (!$keluhan) {
            abort(404);
        }
        
        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $keluhan->delete();
            $response = [
                'message' => 'Deleted Successfully!',
                'keluhan_id' => $id
            ];

            // Response Accept : 'application/json'
            if ($acceptHeader === 'application/json') {
                return response()->json($response, 200);
            } 
            
            // Response Accept : 'application/xml'
            else {
                $xml = new \SimpleXMLElement('<Keluhan/>');

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