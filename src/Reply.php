<?php

declare(strict_types=1);

namespace fishze;

use Psr\SimpleCache\InvalidArgumentException;
use ZM\Exception\OneBot12Exception;

class Reply
{
    /**
     * @throws InvalidArgumentException|OneBot12Exception
     */
    #[\BotEvent(type: 'message')]
    public function onGroupMessage(\OneBotEvent $event): void
    {
        $u = $event->getGroupId() == 0 ? kv("AUTO_REPLY")->get("private") : kv("AUTO_REPLY")->get("{$event->getGroupId()}");
        if ($u != NULL && array_key_exists($event->getMessageString(), $u)) {
                bot()->reply($u[$event->getMessageString()]);
            }
    }

    /**
     * @throws InvalidArgumentException
     */
    #[\init()]
    public function InitReply(): void
    {
        if (kv()->get("AUTO_REPLY") == NULL) {
            kv()->set("AUTO_REPLY", array());
        }
        if (config('auto-reply') === null) {
            logger()->notice('自动回复还没有配置文件，正在为你生成，请到 config/auto-reply.json 填入你的配置项');
            file_put_contents(WORKING_DIR . '/config/auto-reply.json', json_encode(['admin_qq_id' => array()], JSON_PRETTY_PRINT));
        }
    }

    private function getPermission(\OneBotEvent $event): bool
    {
        $all = config("auto-reply.admin_qq_id");
        if ($all == NULL || !in_array($event->getUserId(), $all)) {
            return false;
        }
        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    #[\BotCommand(match: "/群回复")]
    #[\CommandArgument('回复语句')]
    #[\CommandArgument('回复内容')]
    public function AddGroupReply(\BotContext $ctx, \OneBotEvent $event): void
    {
        if ($this->getPermission($event) && $event->getGroupId() != 0) {
            $u = kv("AUTO_REPLY")->get("{$event->getGroupId()}");
            if ($u == NULL) {
                $u = array();
            }
            $u[$ctx->getParam("回复语句")] = $ctx->getParam("回复内容");
            kv("AUTO_REPLY")->set("{$event->getGroupId()}", $u);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    #[\BotCommand(match: "/私聊回复")]
    #[\CommandArgument('回复语句')]
    #[\CommandArgument('回复内容')]
    public function AddPrivateReply(\BotContext $ctx, \OneBotEvent $event): void
    {
        if ($this->getPermission($event) && $event->getGroupId() == 0) {
            $u = kv("AUTO_REPLY")->get("private");
            if ($u == NULL) {
                $u = array();
            }
            $u[$ctx->getParam("回复语句")] = $ctx->getParam("回复内容");
            kv("AUTO_REPLY")->set("private", $u);
        }
    }
}
