<?php

namespace MetaverseSystems\TorrentPHPBackend\Controllers;

use MetaverseSystems\TorrentPHPBackend\Models\Torrent;
use Illuminate\Http\Request;

class TorrentController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $torrents = Torrent::get();
        return $torrents;
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
        $t = new Torrent;
        $t->local_node = 0;
        $t->user_id = 0;
        $t->id = (string)\Str::uuid();
        $t->magnetURI = $request->input('magnetURI');

        if(substr($t->magnetURI, 0, 6) === "magnet")
        {
            $temp = explode("?", $t->magnetURI);
            $vars = explode("&", $temp[1]);
            foreach($vars as $var)
            {
                $parts = explode("=", $var);
                if($parts[0] == "dn") $t->name = urldecode($parts[1]);
            }
        }

        try
        { 
            @$t->save();
        }
        catch(\JsonRPC\Exception\ConnectionFailureException $e)
        {
        }
        catch(\ErrorException $e)
        {
        }

        \Artisan::call('torrent:check');
        return response()->json([], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Torrent  $torrent
     * @return \Illuminate\Http\Response
     */
    public function show(Torrent $torrent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Torrent  $torrent
     * @return \Illuminate\Http\Response
     */
    public function edit(Torrent $torrent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Torrent  $torrent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Torrent $torrent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Torrent  $torrent
     * @return \Illuminate\Http\Response
     */
    public function destroy(Torrent $torrent)
    {
        //
    }
}
