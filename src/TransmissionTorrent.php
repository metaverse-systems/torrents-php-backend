<?php

namespace MetaverseSystems\TorrentPHPBackend;

use Martial\Transmission\API\Argument\Torrent\Add;
use Martial\Transmission\API\Argument\Torrent\Get;
use Martial\Transmission\API\TorrentIdList;

class TransmissionTorrent
{
    private $rpc_url;
    private $httpClient;
    private $api;
    private $sessionId = "";

    public function __construct($url)
    {
        $this->rpc_url = $url;
        $this->httpClient = new \GuzzleHttp\Client(['base_uri' => $this->rpc_url]);
        $this->api = new \Martial\Transmission\API\RpcClient($this->httpClient, "", "");

        try
        {
            $this->api->sessionGet($this->sessionId);
        }
        catch (\Martial\Transmission\API\CSRFException $e)
        {
            // The session has been reinitialized. Fetch the new session ID with the method getSessionId().
            $this->sessionId = $e->getSessionId();
        }
        catch (\Martial\Transmission\API\TransmissionException $e)
        {
            // The API returned an error, retrieve the reason with the method getResult().
            throw new \Exception('       API error: ' . $e->getResult().". When accessing $url.");
        }
    }

    public function add($torrent)
    {
        try
        {
            $result = $this->api->torrentAdd($this->sessionId, [
                \Martial\Transmission\API\Argument\Torrent\Add::FILENAME => $torrent
            ]);

            return $result['hashString'];
        }
        catch (\Martial\Transmission\API\DuplicateTorrentException $e)
        {
            // This torrent is already in your download queue.
        }
        catch (\Martial\Transmission\API\MissingArgumentException $e)
        {
            // Some required arguments are missing.
        }
        catch (\Martial\Transmission\API\CSRFException $e)
        {
             // The session has been reinitialized. Fetch the new session ID with the method getSessionId().
        }
        catch (\Martial\Transmission\API\TransmissionException $e)
        {
            // The API returned an error, retrieve the reason with the method getResult().
            throw new \Exception('API error: ' . $e->getResult());
        }
    }

    public function check($hashString)
    {
        try
        {
            $torrentList = $this->api->torrentGet($this->sessionId,
                new TorrentIdList([ ]), [Get::ID, Get::NAME, Get::STATUS, Get::MAGNET_LINK, Get::HASH_STRING, Get::TORRENT_FILE ]);
        }
        catch (\Martial\Transmission\API\TransmissionException $e)
        {
            // The API returned an error, retrieve the reason with the method getResult().
            die('API error: ' . $e->getResult());
        }

        foreach($torrentList as $torrent)
        {
            if($hashString == $torrent['hashString'])
            {
                // 6 == Complete
                if($torrent['status'] == 6) return true;
            }
        }
        return false;
    }

    public function remove($hashString)
    {
        try
        {
            $torrentList = $this->api->torrentGet($this->sessionId,
                new TorrentIdList([ ]), [Get::ID, Get::NAME, Get::STATUS, Get::MAGNET_LINK, Get::HASH_STRING, Get::TORRENT_FILE ]);
        }
        catch (\Martial\Transmission\API\TransmissionException $e)
        {
            // The API returned an error, retrieve the reason with the method getResult().
            die('API error: ' . $e->getResult());
        }

        foreach($torrentList as $torrent)
        {
            if($hashString == $torrent['hashString'])
            {
                $this->api->torrentRemove($this->sessionId, new TorrentIdList([ $torrent['id'] ]), false);
            }
        }
    }
}
