<?php

namespace MetaverseSystems\TorrentPHPBackend\RemoteTorrents;
use MetaverseSystems\TorrentPHPBackend\RemoteTorrent;

class TransmissionTorrent extends RemoteTorrent
{
    public static $type = "Transmission";
    private $rpc_url;
    private $headers;

    public function __construct($address)
    {
        $this->rpc_url = "http://$address/transmission/rpc";

        @file_get_contents($this->rpc_url);
        foreach($http_response_header as $line)
        {
            $header = explode(":", $line);
            if(count($header) < 2) continue;
            if($header[0] == "X-Transmission-Session-Id")
            {
                $this->headers = "X-Transmission-Session-Id: ".$header[1]."\r\nContent-type: application/json";
            }
        }
    }

    public function add($torrent)
    {
        $response = $this->execute("torrent-add", array("filename"=>$torrent));
        foreach(json_decode($response)->arguments as $k=>$v) return $v->hashString;
    }

    public function check($hashString)
    {
        $response = $this->execute("torrent-get", array("fields"=>["id", "hashString", "status", "magnetLink", "torrentFile"]));
        $torrentList = json_decode($response)->arguments->torrents;
        foreach($torrentList as $torrent)
        {
            if($hashString == $torrent->hashString)
            {
                // 6 == Complete
                if($torrent->status == 6) return true;
            }
        }
        return false;
    }

    public function remove($hashString)
    {
        $response = $this->execute("torrent-get", array("fields"=>["id", "hashString", "status", "magnetLink", "torrentFile"]));
        $torrentList = json_decode($response)->arguments->torrents;

        foreach($torrentList as $torrent)
        {
            if($hashString == $torrent->hashString)
            {
                $this->execute("torrent-remove", array("ids"=>[$torrent->id]));
            }
        }
    }

    private function execute($method, $args)
    {
        $req = new \stdClass;
        $req->id = rand();
        $req->jsonrpc = "2.0";
        $req->method = $method;
        $req->arguments = $args;

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => $this->headers,
                'content' => json_encode($req)
            )
        );

        $context = stream_context_create($opts);
        $response = file_get_contents($this->rpc_url, false, $context);
        return $response;
    }
}
