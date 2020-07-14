<?php

/*
 * JavaMapConverter v1.0.0 by korado531m7
 * Developer: korado531m7
 * Copyright (C) 2020 korado531m7
 * Licensed under MIT (https://github.com/korado531m7/JavaMapConverter/blob/master/LICENSE)
 */

namespace korado531m7\JavaMapConverter;


use korado531m7\JavaMapConverter\data\BlockId;
use korado531m7\JavaMapConverter\data\Face;
use korado531m7\JavaMapConverter\task\AsyncConvertTask;
use pocketmine\level\format\Chunk;
use pocketmine\Player;
use pocketmine\tile\Sign;


class BlockFixer{
    /** @var Main */
    private $instance;
    /** @var bool[][] */
    private $fixed = [];

    public function __construct(Main $instance){
        $this->instance = $instance;
    }

    public function isFixedCoordinates(int $x, int $z) : bool{
        return isset($this->fixed[$x][$z]);
    }

    public function pushFixedCoordinates(int $x, int $z) : void{
        $this->fixed[$x][$z] = true;
    }

    public function fix(Chunk $chunk) : void{
        if($this->isFixedCoordinates($chunk->getX(), $chunk->getZ())){
            return;
        }
        $this->pushFixedCoordinates($chunk->getX(), $chunk->getZ());
        $this->instance->addProcessing();

        if($this->instance->isEnabledRemoveAllEntities()){
            foreach($chunk->getEntities() as $entity){
                if(!$entity instanceof Player){
                    $entity->close();
                }
            }
        }

        if($this->instance->isEnabledSignConvert()){
            foreach($chunk->getTiles() as $tile){
                if($tile instanceof Sign){
                    $texts = $tile->getText();
                    $rawLine1 = @json_decode($texts[0], true);
                    $rawLine2 = @json_decode($texts[1], true);
                    $rawLine3 = @json_decode($texts[2], true);
                    $rawLine4 = @json_decode($texts[3], true);
                    if(is_array($rawLine1) && is_array($rawLine2) && is_array($rawLine3) && is_array($rawLine4)){
                        $tile->setText($rawLine1['text'] ?? '', $rawLine2['text'] ?? '', $rawLine3['text'] ?? '', $rawLine4['text'] ?? '');
                        $tile->saveNBT();
                    }
                }
            }
        }

        if($this->instance->isAsyncEnabled()){
            $this->instance->getServer()->getAsyncPool()->submitTask(new AsyncConvertTask($chunk));
        }else{
            self::convert($chunk);
            $this->instance->subtractProcessing();
        }
    }

    public static function convert(Chunk $chunk) : void{
        for($x = 0; $x < 4 * 4; ++$x){
            for($z = 0; $z < 4 * 4; ++$z){
                for($y = 0; $y < $chunk->getMaxY(); ++$y){
                    $oldBlockId = $chunk->getBlockId($x, $y, $z);
                    $newBlockFace = Face::getNewFace($oldBlockId, $chunk->getBlockData($x, $y, $z));
                    $chunk->setBlock($x, $y, $z, BlockId::getNewBlockId($oldBlockId), $newBlockFace);
                }
            }
        }
    }
}