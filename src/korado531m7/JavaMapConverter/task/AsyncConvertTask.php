<?php

/*
 * JavaMapConverter v1.0.3 by korado531m7
 * Developer: korado531m7
 * Copyright (C) 2020 korado531m7
 * Licensed under MIT (https://github.com/korado531m7/JavaMapConverter/blob/master/LICENSE)
 */

namespace korado531m7\JavaMapConverter\task;


use korado531m7\JavaMapConverter\BlockFixer;
use korado531m7\JavaMapConverter\Main;
use pocketmine\level\format\Chunk;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class AsyncConvertTask extends AsyncTask{
    /** @var string */
    private $chunk;

    public function __construct(Chunk $chunk){
        $this->chunk = $chunk->fastSerialize();
    }

    public function onRun(){
        $chunk = Chunk::fastDeserialize($this->chunk);
        BlockFixer::convert($chunk);
        $this->setResult($chunk->fastSerialize());
    }

    public function onCompletion(Server $server){
        if($this->hasResult()){
            $chunk = Chunk::fastDeserialize($this->getResult());
            $server->getDefaultLevel()->setChunk($chunk->getX(), $chunk->getZ(), $chunk, false);
        }
        Main::subtractProcessing();
    }
}