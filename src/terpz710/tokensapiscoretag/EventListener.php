<?php

declare(strict_types=1);

namespace terpz710\tokensapiscoretag;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\player\Player;

use pocketmine\Server;

use function class_exists;
use function number_format;

use terpz710\tokensapi\TokensAPI;

use terpz710\tokensapi\event\TokenBalanceChangeEvent;

use Ifera\ScoreHud\ScoreHud;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\event\PlayerTagsUpdateEvent;

class EventListener implements Listener {

    protected function updateTag(Player|string $player) : void{
        if (class_exists(ScoreHud::class)) {
            if ($player instanceof Player) {
                $balance = TokensAPI::getInstance()->getTokens($player);
            } else {
                $balance = TokensAPI::getInstance()->getTokens($player);
                $player = Server::getInstance()->getPlayerExact($player);
            }

            if ($player instanceof Player) {
                $ev = new PlayerTagsUpdateEvent(
                    $player,
                    [
                        new ScoreTag("tokensapi.balance", number_format((float)$balance)),
                    ]
                );
                $ev->call();
            }
        }
    }

    public function join(PlayerJoinEvent $event) : void{
        $this->updateTag($event->getPlayer());
    }

    public function change(TokenBalanceChangeEvent; $event) : void{
        $this->updateTag($event->getPlayer());
    }

    public function resolve(TagsResolveEvent $event) : void{
        $player = $event->getPlayer();
        $tag = $event->getTag();

        $tokens = TokensAPI::getInstance();

        $balance = $tokens->hasTokenBalance($player)
            ? $tokens->getTokens($player)
            : 0;

        match ($tag->getName()) {
            "tokensapi.balance" => $tag->setValue(number_format((float)$balance)),
            default => null,
        };
    }
}
