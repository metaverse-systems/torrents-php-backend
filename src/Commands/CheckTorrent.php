<?php

namespace MetaverseSystems\TorrentPHPBackend\Commands;

use Illuminate\Console\Command;
use MetaverseSystems\TorrentPHPBackend\TransmissionTorrent;
use MetaverseSystems\TorrentPHPBackend\Models\Torrent;

class CheckTorrent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'torrent:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process waiting torrent jobs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $torrents = Torrent::whereNull('completed_at')->get();
        if(count($torrents) == 0) return;

        // TODO: pull this from a table
        $transmission = new TransmissionTorrent("http://torrent.homenet:9091/transmission/rpc");

        foreach($torrents as $torrent)
        {
            if($torrent->hash_string)
            {
                // Already added to Transmission
                $check = $transmission->check($torrent->hash_string);
                if($check)
                {
                    $transmission->remove($torrent->hash_string);
                    $torrent->completed_at = date("Y-m-d H:i:s");
                    try
                    {
                        $torrent->save();
                    } catch(JsonRPC\Exception\ConnectionFailureException | \ErrorException $e)
                    {
                    }
                }
            }
            else
            {
print_r($torrent->toArray());
                $torrent->hash_string = $transmission->add($torrent->magnetURI);
                try
                {
                    $torrent->save();
                } catch(JsonRPC\Exception\ConnectionFailureException | \ErrorException $e)
                {
                }
            }
        }
    }
}
