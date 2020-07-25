<?php
namespace App\Tasks;

use App\Module\Base;
use App\Module\Chat;
use App\Module\Umeng;
use App\Module\Users;
use DB;
use Hhxsv5\LaravelS\Swoole\Task\Task;

class NotificationTask extends Task
{
    private $contentId;

    /**
     * NotificationTask constructor.
     * @param int $contentId
     */
    public function __construct($contentId)
    {
        $this->contentId = intval($contentId);
    }

    public function handle()
    {
        $row = Base::DBC2A(DB::table('chat_msg')->where('id', $this->contentId)->first());
        if (empty($row)) {
            return;
        }
        if ($row['roger']) {
            return;
        }
        //
        $message = Base::string2array($row['message']);
        $lists = Base::DBC2A(DB::table('umeng')->where('username', $row['username'])->get());
        foreach ($lists AS $item) {
            Umeng::notification($item['platform'], $item['token'], Users::nickname($row['username']), Chat::messageDesc($message), [
                'contentId' => $row['contentId'],
                'username' => $row['username'],
            ]);
        }
    }
}
