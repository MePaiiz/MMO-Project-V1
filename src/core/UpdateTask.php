<?php

namespace core;

use pocketmine\scheduler\Task;

use core\Main;

class UpdateTask extends Task{
	
	public function __construct(Main $main){
		$this->main = $main;
		}
	public function onRun($currentTick){
		$this->main->onRun();
		}
	}