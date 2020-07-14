<?php

/*
 * JavaMapConverter v1.0.0 by korado531m7
 * Developer: korado531m7
 * Copyright (C) 2020 korado531m7
 * Licensed under MIT (https://github.com/korado531m7/JavaMapConverter/blob/master/LICENSE)
 */

namespace korado531m7\JavaMapConverter;


use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{
    /** @var int */
    private static $runningCount = 0;
    /** @var int */
    private static $allCount = 0;

    /** @var BlockFixer */
    private $blockFixer;

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->blockFixer = new BlockFixer($this);
        $this->getLogger()->alert('This plugin will convert your worlds immediately after player joined the server (chunks were loaded)');
        if($this->isAsyncEnabled()){
            $this->getLogger()->info('Asynchronous mode is enabled. Conversion will run asynchronously');
        }else{
            $this->getLogger()->warning('Asynchronous mode is disabled. It causes a lag while converting');
        }
        if($this->isOutputProgress()){
            $this->getLogger()->warning('Output Progress mode is enabled. All converting progress will be print to console.');
        }
    }

    public function onDisable(){
        if(self::getProcessingCount() >= 1){
            $this->getLogger()->alert('This plugin has been disabled, but conversion hasn\'t finished yet');
        }
    }

    public function onChunkLoad(ChunkLoadEvent $event) : void{
        $this->blockFixer->fix($event->getChunk());
        if($this->isOutputProgress()){
            $this->getLogger()->info('Converting Chunk ' . self::getProcessingCount() . '/' . self::getAllCount());
        }
    }

    public function isOutputProgress() : bool{
        return $this->getConfig()->get('enable-output-progress', false);
    }

    public function isAsyncEnabled() : bool{
        return $this->getConfig()->get('enable-async-mode', true);
    }

    public static function addProcessing() : void{
        ++self::$runningCount;
        ++self::$allCount;
    }

    public static function subtractProcessing() : void{
        --self::$runningCount;
    }

    public static function getProcessingCount() : int{
        return self::$runningCount;
    }

    public static function getAllCount() : int{
        return self::$allCount;
    }
}