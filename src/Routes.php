<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware'=>['api'], 'prefix'=>'api' ], function () {
    Route::get('torrent', 'MetaverseSystems\TorrentPHPBackend\Controllers\TorrentController@index');
    Route::post('torrent', 'MetaverseSystems\TorrentPHPBackend\Controllers\TorrentController@store');
    Route::get('remotetorrent', 'MetaverseSystems\TorrentPHPBackend\Controllers\RemoteTorrentController@index');
    Route::post('remotetorrent', 'MetaverseSystems\TorrentPHPBackend\Controllers\RemoteTorrentController@store');
    Route::delete('remotetorrent/{id}', 'MetaverseSystems\TorrentPHPBackend\Controllers\RemoteTorrentController@destroy');
});
