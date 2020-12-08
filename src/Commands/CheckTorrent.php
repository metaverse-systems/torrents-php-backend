<?php

namespace MetaverseSystems\TorrentPHPBackend\Commands;

use Illuminate\Console\Command;
use MetaverseSystems\TorrentPHPBackend\Models\Torrent;
use MetaverseSystems\TorrentPHPBackend\Models\RemoteTorrent;

class CheckTorrent extends Command
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

        $local_node = env('CLARION_NODE');
        $daemons = RemoteTorrent::where('local_node', $local_node)->get();
        if(count($daemons) == 0)
        {
            print "Visit ".env('APP_URL')."/torrents/remotetorrents to configure address of torrent daemon(s)\n";
            return;
        }
        $daemon = $daemons[rand(0, count($daemons) - 1)];

        $torrentd = null;

        foreach($this->getClasses() as $classname)
        {
            if($classname::getType() == $daemon->type)
            {
                $torrentd = new $classname($daemon->address);
                break;
            }
        }

        if($torrentd == null) throw new \Exception('Could not find an interface for: '.$daemon->type);

        foreach($torrents as $torrent)
        {
            if($torrent->hash_string)
            {
                // Already added
                $check = $torrentd->check($torrent->hash_string);
                if($check)
                {
                    $torrentd->remove($torrent->hash_string);
                    $torrent->completed_at = date("Y-m-d H:i:s");
                    $torrent->save();
                }
            }
            else
            {
                $torrent->hash_string = $torrentd->add($torrent->magnetURI);
                $torrent->save();
            }
        }
    }
}
