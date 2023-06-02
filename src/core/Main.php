<?php

namespace core;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\command\{CommandSender, Command};
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\entity\Entity;
use pocketmine\event\player\{PlayerJoinEvent, PlayerDeathEvent, PlayerQuitEvent};
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\StringTag;
use _64FF00\PureChat\PureChat;

use core\Exp;
use core\UpdateTask;

use core\Run;

class Main extends PluginBase implements Listener{

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig(); 
        $this->reloadConfig(); 
        $this->JoinTitle = $this->getConfig()->get("JoinTitle"); 
        $this->JoinSubtitle = $this->getConfig()->get("JoinSubtitle"); 
        $this->WorldTitle = $this->getConfig()->get("WorldTitle"); 
        $this->WorldSubtitle = $this->getConfig()->get("WorldSubtitle"); 
        $this->Fadein = $this->getConfig()->get("Fadein")*20; $this->Duration = $this->getConfig()->get("Duration")*20; 
        $this->Fadeout = $this->getConfig()->get("Fadeout")*20; $this->getLogger()->info("Đã được kích hoạt!"); 
        if($this->JoinTitle === "" OR $this->JoinSubtitle === "" OR $this->WorldTitle === "" OR $this->WorldSubtitle === ""){ 
            $this->getLogger()->warning('Hãy sửa config.yml lại cho đúng!');
            $this->setEnabled(false); 
        } 
        @mkdir($this->getDataFolder());
        $this->death = new Config($this->getDataFolder()."death.yml", Config::YAML);
        $this->kill = new Config($this->getDataFolder()."kill.yml", Config::YAML);
        $this->point = new Config($this->getDataFolder()."point.yml", Config::YAML);
        $this->levelexp = new Config($this->getDataFolder()."levelexp.yml", Config::YAML);
        $this->exppoint = new Config($this->getDataFolder()."exppoint.yml", Config::YAML);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new Run($this), 20 * 1);
        $this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        $this->PP = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new UpdateTask($this), 20 * 5);
    }
    
    public function onWorldChange(EntityLevelChangeEvent $event){ 
        $player = $event->getEntity(); 
        $task = new SendTitle($this, $event->getEntity(), str_replace(array('{player}', '{world}'), array($event->getEntity()->getName(), $event->getEntity()->getLevel()->getName()), $this->WorldTitle), str_replace(array('{player}', '{world}'), array($event->getEntity()->getName(), $event->getEntity()->getLevel()->getName()), $this->WorldSubtitle), $this->Fadein, $this->Duration, $this->Fadeout); 
        $this->getServer()->getScheduler()->scheduleDelayedTask($task, 20); 
    }
    
    public function onJoin(PlayerJoinEvent $ev){
        $p = $ev->getPlayer();
        $p->setMaxHealth($this->levelexp->get($p->getName()) * 0.50 + 20);
        $p->setHealth($this->levelexp->get($p->getName()) * 0.50 + 20);
        $name = $ev->getPlayer()->getName();
        $message = rand(1,5);
        $this->bar[strtolower($p->getName())] = [];
        $task = new SendTitle($this, $ev->getPlayer(), str_replace(array('{player}', '{world}'), array($ev->getPlayer()->getName(), $ev->getPlayer()->getLevel()->getName()), $this->JoinTitle), str_replace(array('{player}', '{world}'), array($ev->getPlayer()->getName(), $ev->getPlayer()->getLevel()->getName()), $this->JoinSubtitle), $this->Fadein, $this->Duration, $this->Fadeout); 
        $this->getServer()->getScheduler()->scheduleDelayedTask($task, 20); 
        switch($message){
            case 1:
                $ev->setJoinMessage("§l§8(§r§l§8)§r§e ".$name."§f เข้าร่วมเซิฟเวอร์!");
            break;
            
            case 2:
                $ev->setJoinMessage("§l§8(§r§l§8)§r§e ".$name."§f กลับมาอีกครั้ง!");
            break;
            
            case 3:
                $ev->setJoinMessage("§l§8(§r§l§8)§r§e ".$name."§f หวัดดีทุกโคนนนนนน!");
            break;
            
            case 4:
                $ev->setJoinMessage("§l§8(§r§l§8)§r§e ".$name."§f ฉันกลับมาแล้ว!");
            break;
            
            case 5:
                $ev->setJoinMessage("§l§8(§r§l§8)§r§e ".$name."§f คิดถึงจังเลย!");
            break;
        }
        if(!$this->kill->get($p->getName())){
            $this->kill->set($p->getName(), 0);
            $this->kill->save();
        }
        if(!$this->death->get($p->getName())){
            $this->death->set($p->getName(), 0);
            $this->death->save();
        }
        if(!$this->point->get($p->getName())){
            $this->point->set($p->getName(), 0);
            $this->point->save();
        }
        if(!$this->exppoint->get($p->getName())){
            $this->exppoint->set($p->getName(), 0);
            $this->exppoint->save();
        }
        if(!$this->levelexp->get($p->getName())){
            $this->levelexp->set($p->getName(), 0);
            $this->levelexp->save();
        }
    }
    
    public function onPlayerQuit(PlayerQuitEvent $event){
        $name = $event->getPlayer()->getName();
        $message = rand(1,5);
        switch($message){
            case 1:
                $event->setQuitMessage("§l§8(§r§l§8)§r§e ".$name."§f ออกจากเซิฟเวอร์!");
            break;
            
            case 2:
                $event->setQuitMessage("§l§8(§r§l§8)§r§e ".$name."§f เดี๋ยวมาใหม่น้าา!");
            break;
            
            case 3:
                $event->setQuitMessage("§l§8(§r§l§8)§r§e ".$name."§f ไว้เจอกันอีกครั้ง!");
            break;
            
            case 4:
                $event->setQuitMessage("§l§8(§r§l§8)§r§e ".$name."§f นอนก่อนล่ะ!");
            break;
            
            case 5:
                $event->setQuitMessage("§l§8(§r§l§8)§r§e ".$name."§f ขอตัวไปกินหนม!");
            break;
        }
    }
    
    public function update(){
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $name = $p->getName();
            $expname = $this->levelexp->get($p->getName());
            $nametag = "§r §2N§aa§2m§ae§7:§r ".$p->getName()."\n§r §6L§ee§6v§ee§6l§7:§r ".$expname."\n§r §4H§ce§4a§cl§4t§ch§7: §f".$p->getHealth()."§8/§r".$p->getMaxHealth()."";
            $p->setNameTag($nametag);
            $p->setDisplayName("§7(§bLv.§f".$this->levelexp->get($p->getName())."§7)§r ".$name);
            $p->setMaxHealth($this->levelexp->get($p->getName()) * 0.50 + 20);
        }
    }

public function onFight(EntityDamageEvent $event) {
    if($event instanceof EntityDamageByEntityEvent) {
        $hit = $event->getEntity();
        $damager = $event->getDamager();
        if($damager->getItemInHand()->getId() == Item::STICK) {
                      $event->setKnockBack(0.6);
                      $hit->setOnFire(5);
                      $event->setDamage(100);
       }
                  }
                } //Wand
    
    public function myExp($p){
        return $this->exppoint->get($p->getName());
    }
    
    public function setExp($p, $count){
        $this->exppoint->set($p->getName(), $count);
        $this->exppoint->save();
    }
    
    public function onDeath(PlayerDeathEvent $ev){
        $p = $ev->getPlayer();
        $last = $p->getLastDamageCause();
        $this->death->set($p->getName(), $this->death->get($p->getName()) + 1);
        $this->addExp($p, -5);
        $this->death->save();
        if($last instanceof EntityDamageByEntityEvent){
            $killer = $last->getDamager();
            if($killer instanceof Player){
                $this->kill->set($killer->getName(), $this->kill->get($killer->getName()) + 1);
                $this->kill->save();
                if($killer->getLevel()->getName() == "world"){ #ชื่อโลก
                    $this->point->set($killer->getName(), $this->point->get($killer->getName()) + rand(5, 10)); #ปรับอัตราสุ่ม
                    $this->addExp($killer, 10);
                    $killer->sendPopup("§b+ 10 EXP");
                    $this->point->save();
                }
            }
        }
    }
    
    public function onEntityDeath(EntityDeathEvent $ev){
        $entity = $ev->getEntity();
        if($entity->getLastDamageCause() instanceof EntityDamageByEntityEvent){
            $killer = $entity->getLastDamageCause()->getDamager();
            if($killer instanceof Player){
                $this->addExp($killer, 10);
                $killer->sendPopup("§b+ 10 EXP");
            }
        }
    }
    
    public function addExp($p, $count){
        if($p instanceof Player){
            $this->exppoint->set($p->getName(), $this->myExp($p) + $count);
            $this->exppoint->save();
            if($this->myExp($p) >= 200){
                $xp = $count / 100;
                $tipexp = abs((100 * round($xp, 0)) - $count);
                $this->exppoint->set($p->getName(), $tipexp);
                $this->exppoint->save();
                $p->addTitle("§aLevelUp §f+§b");
                $p->setXpLevel($p->getXpLevel() + round($xp, 0));
            }
            if($this->myExp($p) >= 100 && $this->myExp($p) <= 200){
                $this->exppoint->set($p->getName(), $this->myExp($p) - 100);
                $this->exppoint->save();
                $p->addTitle("§aLevelUp §f+§b");
                $this->levelexp->set($p->getName(), $this->levelexp->get($p->getName()) + 1);
                $this->levelexp->save();
            }
        }
    }
    
    public function onCommand(CommandSender $p, Command $cmd, $label, array $args){
        $p2 = $p->getPlayer();
        if($cmd->getName() == "settopexp"){
            if(!$p->isOp()){
                $p->sendMessage("ไม่สามารถใช้คำสั่งได้");
            }
			$p->sendMessage("เพิ่มอันดับ Exp เรียบร้อย");
			$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
			new DoubleTag("", $p->x), 
			new DoubleTag("", $p->y), 
			new DoubleTag("", $p->z)]), 
			"Motion" => new ListTag("Motion", [
			new DoubleTag("", 0),
			new DoubleTag("", 0),
			new DoubleTag("", 0)]), 
			"Rotation" => new ListTag("Rotation", [
			new DoubleTag("", $p->yaw), 
			new DoubleTag("", $p->pitch)]), 
			"Skin" => new CompoundTag("Skin", [
			"Data" => new StringTag("Data", $p->getSkinData()), 
			"Name" => new StringTag("Name", $p->getSkinId())])]);
			$npc = new Exp($p->level, $nbt);
			$npc->setImmobile(true);
			$npc->setNameTagAlwaysVisible(true);
			$npc->setScale(0);
			$npc->spawnToAll();
            return true;
            }
		if($cmd->getName() == "deltopexp"){
            if(!$p->isOp()){
                $p->sendMessage("ไม่สามารถใช้คำสั่งได้");
                return true;
            }
            $p->sendMessage("ลบอันดับ Exp เรียบร้อย");
			foreach($this->getServer()->getLevels() as $level){
				foreach($level->getEntities() as $entity){
					if($entity instanceof Exp){
						$entity->close();
                        return true;
					}
				}
			}
        }
        if($cmd->getName() == "settoplevel"){
            if(!$p->isOp()){
                $p->sendMessage("ไม่สามารถใช้คำสั่งได้");
            }
			$p->sendMessage("เพิ่มอันดับ Level เรียบร้อย");
			$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
			new DoubleTag("", $p->x), 
			new DoubleTag("", $p->y), 
			new DoubleTag("", $p->z)]), 
			"Motion" => new ListTag("Motion", [
			new DoubleTag("", 0),
			new DoubleTag("", 0),
			new DoubleTag("", 0)]), 
			"Rotation" => new ListTag("Rotation", [
			new DoubleTag("", $p->yaw), 
			new DoubleTag("", $p->pitch)]), 
			"Skin" => new CompoundTag("Skin", [
			"Data" => new StringTag("Data", $p->getSkinData()), 
			"Name" => new StringTag("Name", $p->getSkinId())])]);
			$npc = new Levelexp($p->level, $nbt);
			$npc->setImmobile(true);
			$npc->setNameTagAlwaysVisible(true);
			$npc->setScale(0);
			$npc->spawnToAll();
            return true;
            }
		if($cmd->getName() == "deltoplevel"){
            if(!$p->isOp()){
                $p->sendMessage("ไม่สามารถใช้คำสั่งได้");
                return true;
            }
            $p->sendMessage("ลบอันดับ Level เรียบร้อย");
			foreach($this->getServer()->getLevels() as $level){
				foreach($level->getEntities() as $entity){
					if($entity instanceof Levelexp){
						$entity->close();
                        return true;
					}
				}
			}
        }
        if($cmd->getName() == "settoppoint"){
            if(!$p->isOp()){
                $p->sendMessage("ไม่สามารถใช้คำสั่งได้");
            }
			$p->sendMessage("เพิ่มอันดับ point เรียบร้อย");
			$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
			new DoubleTag("", $p->x), 
			new DoubleTag("", $p->y), 
			new DoubleTag("", $p->z)]), 
			"Motion" => new ListTag("Motion", [
			new DoubleTag("", 0),
			new DoubleTag("", 0),
			new DoubleTag("", 0)]), 
			"Rotation" => new ListTag("Rotation", [
			new DoubleTag("", $p->yaw), 
			new DoubleTag("", $p->pitch)]), 
			"Skin" => new CompoundTag("Skin", [
			"Data" => new StringTag("Data", $p->getSkinData()), 
			"Name" => new StringTag("Name", $p->getSkinId())])]);
			$npc = new Pvppoint($p->level, $nbt);
			$npc->setImmobile(true);
			$npc->setNameTagAlwaysVisible(true);
			$npc->setScale(0);
			$npc->spawnToAll();
            return true;
            }
		if($cmd->getName() == "deltoppoint"){
            if(!$p->isOp()){
                $p->sendMessage("ไม่สามารถใช้คำสั่งได้");
                return true;
            }
            $p->sendMessage("ลบอันดับ point เรียบร้อย");
			foreach($this->getServer()->getLevels() as $level){
				foreach($level->getEntities() as $entity){
					if($entity instanceof Pvppoint){
						$entity->close();
                        return true;
					}
				}
			}
        }
        if($cmd->getName() == "settopkill"){
            if(!$p->isOp()){
                $p->sendMessage("ไม่สามารถใช้คำสั่งได้");
            }
			$p->sendMessage("เพิ่มอันดับ kill เรียบร้อย");
			$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
			new DoubleTag("", $p->x), 
			new DoubleTag("", $p->y), 
			new DoubleTag("", $p->z)]), 
			"Motion" => new ListTag("Motion", [
			new DoubleTag("", 0),
			new DoubleTag("", 0),
			new DoubleTag("", 0)]), 
			"Rotation" => new ListTag("Rotation", [
			new DoubleTag("", $p->yaw), 
			new DoubleTag("", $p->pitch)]), 
			"Skin" => new CompoundTag("Skin", [
			"Data" => new StringTag("Data", $p->getSkinData()), 
			"Name" => new StringTag("Name", $p->getSkinId())])]);
			$npc = new Kill($p->level, $nbt);
			$npc->setImmobile(true);
			$npc->setNameTagAlwaysVisible(true);
			$npc->setScale(0);
			$npc->spawnToAll();
            return true;
            }
		if($cmd->getName() == "deltopkill"){
            if(!$p->isOp()){
                $p->sendMessage("ไม่สามารถใช้คำสั่งได้");
                return true;
            }
            $p->sendMessage("ลบอันดับ kill เรียบร้อย");
			foreach($this->getServer()->getLevels() as $level){
				foreach($level->getEntities() as $entity){
					if($entity instanceof Kill){
						$entity->close();
                        return true;
					}
				}
			}
        }
        if($cmd->getName() == "settopdeath"){
            if(!$p->isOp()){
                $p->sendMessage("ไม่สามารถใช้คำสั่งได้");
            }
			$p->sendMessage("เพิ่มอันดับ death เรียบร้อย");
			$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
			new DoubleTag("", $p->x), 
			new DoubleTag("", $p->y), 
			new DoubleTag("", $p->z)]), 
			"Motion" => new ListTag("Motion", [
			new DoubleTag("", 0),
			new DoubleTag("", 0),
			new DoubleTag("", 0)]), 
			"Rotation" => new ListTag("Rotation", [
			new DoubleTag("", $p->yaw), 
			new DoubleTag("", $p->pitch)]), 
			"Skin" => new CompoundTag("Skin", [
			"Data" => new StringTag("Data", $p->getSkinData()), 
			"Name" => new StringTag("Name", $p->getSkinId())])]);
			$npc = new Death($p->level, $nbt);
			$npc->setImmobile(true);
			$npc->setNameTagAlwaysVisible(true);
			$npc->setScale(0);
			$npc->spawnToAll();
            return true;
            }
		if($cmd->getName() == "deltopdeath"){
            if(!$p->isOp()){
                $p->sendMessage("ไม่สามารถใช้คำสั่งได้");
                return true;
            }
            $p->sendMessage("ลบอันดับ Death เรียบร้อย");
			foreach($this->getServer()->getLevels() as $level){
				foreach($level->getEntities() as $entity){
					if($entity instanceof Death){
						$entity->close();
                        return true;
					}
				}
			}
        }
        if($cmd->getName() == "statusbar"){
            if(isset($this->bar[strtolower($p2->getName())])){
				$s->sendMessage("§8(§aStatusbar§8)§f ปิดใช้งานแล้ว!");
				unset($this->bar[strtolower($p2->getName())]);
			} else{
				$s->sendMessage("§8(§aStatusbar§8)§f เปิดใช้งานแล้ว!");
		        $this->bar[strtolower($p2->getName())] = [];
           }
        }
    }
    
    public function onRun(){
		foreach($this->getServer()->getLevels() as $level){
			foreach($level->getEntities() as $entity){
				if($entity instanceof Exp){
					$tops = $this->exppoint->getAll();
			arsort($tops);
			$num = 0;
			$msg = " §7§l(§cT§6O§eP §cE§6X§eP§7) §fอันดับ Exp!!!\n";
			foreach($tops as $key => $value){
				if($num < 10){
					$msg .= "§f".($num+1)." §6> §f".$key." §eมี §f".number_format($value)." §bExp\n";
					}
					$num++;
				}
				$entity->setNameTag($msg);
				$entity->setScale(0);
                }
            }
            foreach($level->getEntities() as $entity){
				if($entity instanceof Pvppoint){
					$tops = $this->point->getAll();
			arsort($tops);
			$num = 0;
			$msg = " §7§l(§cP§6V§eP§a P§bO§dI§cN§6T§7) §fอันดับ Pvp Point!!!\n";
			foreach($tops as $key => $value){
				if($num < 10){
					$msg .= "§f".($num+1)." §6> §f".$key." §eมี §f".number_format($value)." §bPoint\n";
					}
					$num++;
				}
				$entity->setNameTag($msg);
				$entity->setScale(0);
                }
            }
            foreach($level->getEntities() as $entity){
				if($entity instanceof Death){
					$tops = $this->death->getAll();
			arsort($tops);
			$num = 0;
			$msg = " §7§l(§4T§cO§4P§c D§4E§cA§4T§cH§7) §fอันดับ Death!!!\n";
			foreach($tops as $key => $value){
				if($num < 10){
					$msg .= "§f".($num+1)." §6> §f".$key." §eมี §f".number_format($value)." §bDeath\n";
					}
					$num++;
				}
				$entity->setNameTag($msg);
				$entity->setScale(0);
                }
            }
            foreach($level->getEntities() as $entity){
				if($entity instanceof Levelexp){
					$tops = $this->levelexp->getAll();
			arsort($tops);
			$num = 0;
			$msg = " §7§l(§eT§aO§bP §eL§bE§dV§cE§6L§7) §fอันดับ Level!!!\n";
			foreach($tops as $key => $value){
				if($num < 10){
					$msg .= "§f".($num+1)." §6> §f".$key." §eมี §f".number_format($value)." §bLevel\n";
					}
					$num++;
				}
				$entity->setNameTag($msg);
				$entity->setScale(0);
                }
            }
            foreach($level->getEntities() as $entity){
				if($entity instanceof Kill){
					$tops = $this->kill->getAll();
			arsort($tops);
			$num = 0;
			$msg = " §7§l(§2T§aO§2P§a K§2I§aL§2L§7) §fอันดับ Kill!!!\n";
			foreach($tops as $key => $value){
				if($num < 10){
					$msg .= "§f".($num+1)." §6> §f".$key." §eมี §f".number_format($value)." §bKill\n";
					}
					$num++;
				}
				$entity->setNameTag($msg);
				$entity->setScale(0);
                }
            }
        }
	}
    public function onBar(){
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $food = $p->getFood();
            $mfood = $p->getMaxFood();
            $name = $p->getName();
            $xp = $p->getXpLevel();
            $hp = $p->getHealth();
            $total = $p->getTotalXp();
            $mhp = $p->getMaxHealth();
            $online = count($this->getServer()->getOnlinePlayers());
            $maxonline = $this->getServer()->getMaxPlayers();
            if(isset($this->bar[strtolower($p->getName())])){
            if($this->PP){
                $group = $this->PP->getUserDataMgr()->getGroup($p)->getName();
            }else{
                $group = "ไม่มีระบบ";
            }
            if($this->eco){
                $money = $this->eco->myMoney($p);
            }else{
                $money = "ไม่มีระบบ";
            }
            $t = str_repeat(" ", 85);
            $t2 = str_repeat(" ", -7);
            $t3 = str_repeat(" ", 43);
            $t4 = str_repeat(" ", 37);
		    $n = str_repeat("\n", 27);
            $p->sendTip($t3."§r  \n"
.$t4."§eเซิฟเวอร์แนวฟาร์มมอนเตอร์ เก็บเวลชิลๆ\n"
.$t4."§r เลือด §7: §f".$hp."§7/§f".$mhp." §r พลังงาน §7: §f".$food."§7/§f".$mfood."\n"
.$t3."§r ออนไลน์ §7: §f".$online."§7/§f".$maxonline."§f คน\n"
.$t3."\n"
.$t3."\n"
.$t3."\n"
.$t2."§r สถานะการต่อสู้                                                                   §r สถานะตัวละคร \n"
.$t2."§r §cKill §7: §f".$this->kill->get($p->getName())."                                                                           §r §bName §7: §f".$name."\n"
.$t2."§r §6Death §7: §f".$this->death->get($p->getName())."                                                                          §r⩐ §dMoney §7: §f".$money."\n"
.$t2."§r⨯ §ePVP Point §7: §f".$this->point->get($p->getName())."                                                                      §r §cLevel §7: §f".$this->levelexp->get($p->getName())."\n"
.$t2."§r §aRank §7: §f".$group."                                                                           §r §6Exp §7: §f".$this->exppoint->get($p->getName())."§7/§f100".$n);
            }
        }
    }
}