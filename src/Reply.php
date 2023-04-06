<?php

declare(strict_types=1);

namespace fishze;

use Psr\SimpleCache\InvalidArgumentException;

class Reply
{
    /**
     * @throws InvalidArgumentException|\ZM\Exception\OneBot12Exception
     */
    #[\BotEvent(type: 'message', detail_type: 'group')]
    public function onGroupMessage(\OneBotEvent $event): void
    {
        $u = kv("AUTO_REPLY")->get("{$event->getGroupId()}");
        if($u != NULL && array_key_exists($event->getMessageString(), $u)){
            bot() ->reply($u[$event->getMessageString()]);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    #[\init()]
    public function InitReply(): void
    {
        if(kv()->get("AUTO_REPLY") == NULL){
            kv()->set("AUTO_REPLY", array());
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    #[\BotCommand(match: "/群回复")]
    #[\CommandArgument('param1')]
    #[\CommandArgument('param2')]
    public function AddReply(\BotContext $ctx, \OneBotEvent $event) : void
    {
        ob_dump($event->getUserId());
        ob_dump($event->getType());
        if($event->getUserId() == 3053473706 && $event->getGroupId() != 0) {
            $u = kv("AUTO_REPLY")->get("{$event->getGroupId()}");
            if($u == NULL){
                $u = array();
            }
            $u[$ctx->getParam("param1")] = $ctx->getParam("param2");
            kv("AUTO_REPLY")->set("{$event->getGroupId()}", $u);
        }
    }
}
