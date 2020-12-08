<?php

namespace MetaverseSystems\TorrentPHPBackend\Controllers;

use MetaverseSystems\TorrentPHPBackend\Models\RemoteTorrent;
use Illuminate\Http\Request;

class RemoteTorrentController extends \App\Http\Controllers\Controller
{
    private $skip = array(".", "..");

    private function getClasses()
    {
        $classes = array();
        $dir = scandir(__DIR__."/../RemoteTorrents");
        foreach($dir as $file)
        {      
            if(in_array($file, $this->skip)) continue;
            if(stripos($file, ".php") === false) continue;
            if(stripos($file, ".swp") !== false) continue;

            $classname = "MetaverseSystems\\TorrentPHPBackend\\RemoteTorrents\\".str_replace(".php", "", $file);
            array_push($classes, $classname);
        }
        return $classes;
    }

    public function getTypes()
    {   
        $types = array();
        $classes = $this->getClasses();
        foreach($classes as $classname)
        {
            array_push($types, $classname::getType());
        }
        return $types;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = new \stdClass;
        $data->remoteTorrents = RemoteTorrent::get();
        $data->remoteTorrentTypes = $this->getTypes();
        return response()->json($data, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $t = new RemoteTorrent;
        $t->local_node = env('CLARION_NODE');
        $t->id = (string)\Str::uuid();
        $t->address = $request->input('address');
        $t->type = $request->input('type');
        $t->save();

        return response()->json([], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\RemoteTorrent  $remotetorrent
     * @return \Illuminate\Http\Response
     */
    public function show(RemoteTorrent $remotetorrent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\RemoteTorrent  $remotetorrent
     * @return \Illuminate\Http\Response
     */
    public function edit(RemoteTorrent $remotetorrent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RemoteTorrent  $remotetorrent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RemoteTorrent $remotetorrent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RemoteTorrent  $remotetorrent
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $t = RemoteTorrent::find($id);
        $t->delete();
        return response()->json([], 202);
    }
}
