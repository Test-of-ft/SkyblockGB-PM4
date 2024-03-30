<?php

namespace Biswajit\Skyblock\menu;

use pocketmine\Server;
use pocketmine\player\Player;
use Biswajit\Skyblock\API;
use Biswajit\Skyblock\Skyblock;
use Biswajit\Skyblock\EventHandler;
use pocketmine\block\utils\DyeColor;

use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\item\ItemTypeIds;
use pocketmine\block\VanillaBlocks;

use muqsit\invmenu\InvMenu;
use pocketmine\inventory\SimpleInventory;
use pocketmine\data\bedrock\EnchantmentIdMap;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;

use pocketmine\item\ItemFactory;
use pocketmine\color\Color;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\scheduler\ClosureTask;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;

class GUI
{
  
  /** @var InvMenu */
  private $DoubleChest;
  
  /** @var InvMenu */
  private $SingleChest;
  
  /** @var String */
  private $Window;
  
  /** @var bool */
  private $ItemsReturned;
  
  /** @var array */
  private $Listings;
  
  /** @var Instance */
  private static $instance;
  
  /** @var API */
  public $api;
  
  /** @var Skyblock */
  private $source;
  
  /** @var Config */
  public $players;
  
  /** @var Config */
  public $config;
  
  public function __construct(Skyblock $source)
  {
    self::$instance = $this;
    $this->source = $source;
    $this->api = $source->getInstance()->getAPI();
    $this->config = $source->getInstance()->getConfigFile();
    $this->DoubleChest = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
    $this->SingleChest = InvMenu::create(InvMenu::TYPE_CHEST);
    $this->Window = "";
  }
  
  public static function getInstance(): GUI
  {
    return self::$instance;
  }

  public function VisitMenu(Player $player): void
  {
    $menu = $this->DoubleChest;
    $menu->setName("§bElite§3Games");
    $menu->setListener(
      function (InvMenuTransaction $transaction) use ($menu) : InvMenuTransactionResult 
      {
        $itemIn = $transaction->getIn();
        $itemOut = $transaction->getOut();
        $player = $transaction->getPlayer();
        $itemInId = $transaction->getIn()->getId();
        $itemOutId = $transaction->getOut()->getId();
        $itemInMeta = $transaction->getIn()->getMeta();
        $inv = $transaction->getAction()->getInventory();
        $itemOutMeta = $transaction->getOut()->getMeta();
        $playerName = $transaction->getPlayer()->getName();
        $itemInName = $transaction->getIn()->getCustomName();
        $itemOutName = $transaction->getOut()->getCustomName();
        
        if($itemOutId === 397 && $itemOutMeta === 3)
        {
          $visitingPlayer = Server::getInstance()->getPlayerExact(str_replace(["§r §b", " §r"], ["", ""], $itemOut->getCustomName()));
          if(!is_null($visitingPlayer))
          {
          $visitingPlayerName = $visitingPlayer->getName();
          if($visitingPlayer instanceof Player)
          {
            if($this->api->haselitegames($visitingPlayerName))
            {
              $worldName = $this->source->getInstance()->getPlayerFile($visitingPlayerName)->get("Island");
              if(!is_null($worldName))
              {
                Server::getInstance()->getWorldManager()->loadWorld($worldName);
                $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
                if($world !== null)
                {
                  if($world->getFolderName() !== $player->getLocation()->world->getFolderName())
                  {
                    if(!$this->source->getInstance()->getPlayerFile($visitingPlayerName)->getNested("IslandSettings.Locked"))
                    {
                      if(count($world->getPlayers()) < $this->source->getInstance()->getPlayerFile($visitingPlayerName)->getNested("IslandSettings.MaxVisitors"))
                      {
                        $player->teleport($world->getSpawnLocation());
                        $player->sendMessage("§aVisiting §e$visitingPlayerName");
                      }else{
                        $player->sendMessage("§cmaximum number of visitors reached");
                      }
                    }elseif($this->source->getInstance()->getPlayerFile($visitingPlayerName)->getNested("IslandSettings.FriendsVisit"))
                     {
                      $isFriend = false;
                      if(count($this->source->getInstance()->getPlayerFile($visitingPlayerName)->get("Friends")) >= 1)
                      {
                        foreach($this->source->getInstance()->getPlayerFile($visitingPlayerName)->get("Friends") as $friend)
                        {
                          if($friend === $playerName)
                          {
                            $isFriend = true;
                             break;
                          }
                        }
                      }
                      if($isFriend)
                      {
                        if(count($world->getPlayers()) < $this->source->getInstance()->getPlayerFile($visitingPlayerName)->getNested("IslandSettings.MaxVisitors"))
                        {
                          $player->teleport($world->getSpawnLocation());
                          $player->sendMessage("§aVisiting §e$visitingPlayerName");
                        }else{
                          $player->sendMessage("§cMaximum number of visitor reached");
                        }
                      }else{
                        $player->sendMessage("§cIsland is locked");
                      }
                    }else{
                      $player->sendMessage("§cIsland is locked");
                    }
                  }else{
                    $player->sendMessage("§can error occurred");
                  }
                }else{
                  $player->sendMessage("§can error occurred");
                }
              }
            }else{
              $player->sendMessage("§can error occurred");
            }
          }
          }
        }elseif($itemOutId === 262 && $itemOutMeta === 0)
        {
          $this->MainGUI($player);
        }elseif($itemOutId === 331 && $itemOutMeta === 0)
        {
          $player->removeCurrentWindow();
        }
        
        return $transaction->discard();
      }
    );
    $inv = $menu->getInventory();
    $inv->setItem(0, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(1, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(2, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(3, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(4, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(5, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(6, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(7, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(8, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(9, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(10, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(11, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(12, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(13, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(14, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(15, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(16, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(17, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(18, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(19, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(20, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(21, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(22, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(23, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(24, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(25, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(26, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(27, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(28, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(29, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(30, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(31, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(32, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(33, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(34, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(35, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(36, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(37, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(38, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(39, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(40, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(41, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(42, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(43, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(44, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(45, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(46, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(47, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(48, ItemFactory::getInstance()->get(262, 0, 1)->setCustomName("§r §cBack §r\n§r §7click to go back to the privious menu §r"));
    $inv->setItem(49, ItemFactory::getInstance()->get(331, 0, 1)->setCustomName("§r §cExit §r\n§r §7click to exit the menu §r"));
    $inv->setItem(50, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(51, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(52, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(53, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $i = 0;
    foreach(Server::getInstance()->getOnlinePlayers() as $online)
    {
      if($player->getName() !== $online->getName())
      {
        if($i < 7)
        {
          $slot = $i + 10;
          $playerName = $online->getName();
          $inv->setItem($slot, ItemFactory::getInstance()->get(397, 3, 1)->setCustomName("§r §b$playerName §r"));
        }elseif($i < 14)
        {
          $slot = $i + 12;
          $playerName = $online->getName();
          $inv->setItem($slot, ItemFactory::getInstance()->get(397, 3, 1)->setCustomName("§r §b$playerName §r"));
        }elseif($i < 21)
        {
          $slot = $i + 14;
          $playerName = $online->getName();
          $inv->setItem($slot, ItemFactory::getInstance()->get(397, 3, 1)->setCustomName("§r §b$playerName §r"));
        }
        $i++;
      }
    }
    if($this->Window !== "Double-Chest")
    {
      $menu->send($player);
      $this->Window = "Double-Chest";
    }
  }
  
  
  public function ManageMembersMenu(Player $player)
  {
    $menu = $this->DoubleChest;
    $menu->setName("§bMembers §3List");
    $menu->setListener(
      function (InvMenuTransaction $transaction): InvMenuTransactionResult 
      {
        $itemIn = $transaction->getIn();
        $itemOut = $transaction->getOut();
        $player = $transaction->getPlayer();
        $itemInId = $transaction->getIn()->getId();
        $itemOutId = $transaction->getOut()->getId();
        $itemInMeta = $transaction->getIn()->getMeta();
        $inv = $transaction->getAction()->getInventory();
        $itemOutMeta = $transaction->getOut()->getMeta();
        $itemInName = $transaction->getIn()->getCustomName();
        $itemOutName = $transaction->getOut()->getCustomName();
        
        if($itemOutId === 262 && $itemOutMeta === 0)
        {
          $this->SettingsMenu($player);
        }elseif($itemOutId === 331 && $itemOutMeta === 0)
        {
          $player->removeCurrentWindow();
        }elseif($itemOutId === 397 && $itemOutMeta === 3)
        {
          $member = str_replace(["§r §e", " §r"], ["", ""], $itemOutName);
          $this->ManageMemberMenu($player, $member);
        }
        
        return $transaction->discard();
      }
    );
    $inv = $menu->getInventory();
    $inv->setItem(0, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(1, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(2, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(3, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(4, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(5, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(6, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(7, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(8, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(9, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(10, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(11, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(12, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(13, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(14, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(15, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(16, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(17, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(18, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(19, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(20, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(21, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(22, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(23, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(24, ItemFactory::getInstance()->get(0, 0, 0));
    $inv->setItem(25, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(26, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(27, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(28, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(29, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(30, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(31, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(32, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(33, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(34, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(35, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(36, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(37, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(38, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(39, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(40, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(41, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(42, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(43, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(44, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(45, ItemFactory::getInstance()->get(160, 14, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(46, ItemFactory::getInstance()->get(160, 14, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(47, ItemFactory::getInstance()->get(160, 14, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(48, ItemFactory::getInstance()->get(262, 0, 1)->setCustomName("§r §cBack §r\n§r §7click to go back to the privious menu §r"));
    $inv->setItem(49, ItemFactory::getInstance()->get(331, 0, 1)->setCustomName("§r §cExit §r\n§r §7click to exit the menu §r"));
    $inv->setItem(50, ItemFactory::getInstance()->get(160, 14, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(51, ItemFactory::getInstance()->get(160, 14, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(52, ItemFactory::getInstance()->get(160, 14, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(53, ItemFactory::getInstance()->get(160, 14, 1)->setCustomName("§r §7 §r"));
    $i = 1;
    $island = $this->api->getSource()->getPlayerFile($player)->get("Island");
    $members = array();
    foreach(scandir($this->api->getSource()->getDataFolder() . "players") as $key => $file)
    {
      if(is_file($this->api->getSource()->getDataFolder() . "players/$file"))
      {
        $playerFile = new Config($this->api->getSource()->getDataFolder() . "players/$file", Config::YAML, [
          ]);
        if($playerFile->get("Island") === $island && ($playerFile->getNested("Co-Op.Role") === "Owner" || $playerFile->getNested("Co-Op.Role") === "Co-Owner"))
        {
          $members = $playerFile->getNested("Co-Op.Members");
        }
      }
    }
    foreach($members as $member)
    {
      $slot = $i + 19;
      $inv->setItem($slot, ItemFactory::getInstance()->get(397, 3, 1)->setCustomName("§r §e$member §r"));
    }
    if($this->Window !== "Double-Chest")
    {
      $menu->send($player);
      $this->Window = "Double-Chest";
    }
  }
    
  public function ManageMemberMenu(Player $player, string $member)
  {
    $menu = $this->SingleChest;
    $menu->setName("§Manage §3Member");
    $menu->setListener(
      function(InvMenuTransaction $transaction) use($member): InvMenuTransactionResult 
      {
        $itemIn = $transaction->getIn();
        $itemOut = $transaction->getOut();
        $player = $transaction->getPlayer();
        $itemInId = $transaction->getIn()->getId();
        $itemOutId = $transaction->getOut()->getId();
        $itemInMeta = $transaction->getIn()->getMeta();
        $inv = $transaction->getAction()->getInventory();
        $itemOutMeta = $transaction->getOut()->getMeta();
        $itemInName = $transaction->getIn()->getCustomName();
        $itemOutName = $transaction->getOut()->getCustomName();
        
        if($itemOutId === 35)
        {
          if($player->getName() !== $member)
          {
            if($itemOutMeta === 5)
            {
              if($this->api->CoOpPromote($member))
              {
                $role = $this->api->getCoOpRole($member);
                $player->sendMessage("§apromoted §e$member §ato §e$role");
                $player->removeCurrentWindow();
              }
            }elseif($itemOutMeta == 14)
            {
              if($this->api->CoOpDemote($member))
              {
                $role = $this->api->getCoOpRole($member);
                $player->sendMessage("§ademoted §e$member §ato §e$role");
                $player->removeCurrentWindow();
              }
            }
          }
        }elseif($itemOutId === 152)
        {
          if($player->getName() !== $member)
          {
            if($this->api->removeCoOp($member))
            {
              $player->sendMessage("§aremoved §e$member from Co-Op");
            }else{
              $player->sendMessage("§ccan't remove the player from Co-Op");
            }
            $player->removeCurrentWindow();
          }
        }
        
        return $transaction->discard();
      }
    );
    $inv = $menu->getInventory();
    $inv->setItem(0, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(1, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(2, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(3, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(4, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(5, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(6, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(7, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(8, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(9, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(10, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $demotedRole = array(
    "Builder" => "-",
    "Member" => "Builder",
    "Senior-Member" => "Member",
    "Co-Owner" => "Senior-Member",
    "Owner" => "-"
);
$coOpRole = $this->api->getCoOpRole($member);
$demoted = isset($demotedRole[$coOpRole]) ? $demotedRole[$coOpRole] : "";
    $inv->setItem(11, ItemFactory::getInstance()->get(35, 14, 1)->setCustomName("§r §cDemote §r\n§r §7 §r\n§r §7Demoted Role: §e$demoted §r"));
    $inv->setItem(12, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(13, ItemFactory::getInstance()->get(152, 0, 1)->setCustomName("§r §cRemove §r"));
    $inv->setItem(14, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $promotedRole = array(
    "Builder" => "Member",
    "Member" => "Senior-Member",
    "Senior-Member" => "Co-Owner",
    "Co-Owner" => "-",
    "Owner" => "-",
);
    $coOpRole = $this->api->getCoOpRole($member);
    $promoted = isset($promotedRole[$coOpRole]) ? $promotedRole[$coOpRole] : null;
    $inv->setItem(15, ItemFactory::getInstance()->get(35, 5, 1)->setCustomName("§r §aPromote §r\n§r §7 §r\n§r §7Prmoted Role: §e$promoted §r"));
    $inv->setItem(16, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(17, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(18, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(19, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(20, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(21, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(22, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(23, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(24, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(25, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    $inv->setItem(26, ItemFactory::getInstance()->get(160, 3, 1)->setCustomName("§r §7 §r"));
    if($this->Window !== "Single-Chest")
    {
      $menu->send($player);
      $this->Window = "Single-Chest";
    }
}

}
