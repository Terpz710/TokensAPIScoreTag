<?php

declare(strict_types=1);

namespace terpz710\tokensapiscoretag;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\player\Player;

use pocketmine\Server;

use function class_exists;

use Ifera\ScoreHud\ScoreHud;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\event\PlayerTagsUpdateEvent;

use terpz710\tokensapi\TokensAPI;

use terpz710\tokensapi\event\AddTokenEvent;
use terpz710\tokensapi\event\RemoveTokenEvent;
use terpz710\tokensapi\event\SetTokenEvent;

class EventListener implements Listener {

    protected function updateTag(Player|string $player): void {
        if (class_exists(ScoreHud::class)) {
            if ($player instanceof Player) {
                $balance = TokensAPI::getInstance()->getTokenBalance($player);
            } else {
                $balance = TokensAPI::getInstance()->getTokenBalance($player);
                $player = Server::getInstance()->getPlayerExact($player);
            }

            if ($player instanceof Player) {
                $ev = new PlayerTagsUpdateEvent(
                    $player,
                    [
                        new ScoreTag("tokensapi.balance", number_format($balance)),
                    ]
                );
                $ev->call();
            }
        }
    }

    public function join(PlayerJoinEvent $event) : void{
        $player = $event->getPlayer();
        $api = TokensAPI::getInstance();

        if (!$api->hasTokenBalance($player)) {
            $api->createTokenBalance($player);
        }
        
        $this->updateTag($player);
    }

    public function add(AddTokenEvent $event) : void{
        $this->updateTag($event->getPlayer());
    }

    public function remove(RemoveTokenEvent $event) : void{
        $this->updateTag($event->getPlayer());
    }

    public function set(SetTokenEvent $event) : void{
        $this->updateTag($event->getPlayer());
    }

    public function resolve(TagsResolveEvent $event): void {
        $player = $event->getPlayer();
        $tag = $event->getTag();

        $balance = TokensAPI::getInstance()->hasTokenBalance($player)
            ? TokensAPI::getInstance()->getTokenBalance($player)
            : 0;

        match ($tag->getName()) {
            "tokensapi.balance" => $tag->setValue(number_format((float)$balance)),
            default => null,
        };
    }
}
