<?php

namespace Haxibiao\Live\Tests\Feature\GraphQL;

use App\LiveRoom;
use App\User;
use Haxibiao\Base\GraphQLTestCase;

class LiveTest extends GraphQLTestCase
{
    protected $user;
    protected $liveRoom;
    protected $live;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user     = User::take(10)->get()->random();
        $this->live     = $this->user->openLive("测试开直播");
        $this->liveRoom = $this->live->room;
    }

    public function testOpenLiveMutation()
    {
        $mutation = file_get_contents(__DIR__ . '/Live/Mutation/OpenLiveMutation.gql');
        $header   = $this->getHeaders($this->user);
        $data     = array(
            "title" => "测试重开直播，修改标题",
        );

        $this->startGraphQL($mutation, $data, $header);

    }

    public function testJoinLiveMutation()
    {
        $mutation = file_get_contents(__DIR__ . '/Live/Mutation/JoinLiveMutation.gql');
        $header   = $this->getHeaders($this->user);

        $data = array(
            "live_id" => $this->live->id,
        );
        $this->startGraphQL($mutation, $data, $header);
    }

    public function testLeaveLiveMutation()
    {
        $mutation = file_get_contents(__DIR__ . '/Live/Mutation/LeaveLiveMutation.gql');
        $header   = $this->getHeaders($this->user);

        $data = array(
            "live_id" => $this->live->id,
        );
        $this->startGraphQL($mutation, $data, $header);
    }

    public function testCommentLiveMutation()
    {
        $mutation = file_get_contents(__DIR__ . '/Live/Mutation/CommentLiveMutation.gql');
        $header   = $this->getHeaders($this->user);

        $data = array(
            "live_id" => $this->live->id,
            "message" => "张志明真帅",
        );
        $this->startGraphQL($mutation, $data, $header);
    }

    public function testCloseLiveMutation()
    {
        $mutation = file_get_contents(__DIR__ . '/Live/Mutation/CloseLiveMutation.gql');
        $header   = $this->getHeaders($this->user);

        $data = array(
            "live_id" => $this->live->id,
        );
        $this->startGraphQL($mutation, $data, $header);
    }

    // /* --------------------------------------------------------------------- */
    // /* ------------------------------- Query ----------------------------- */
    // /* --------------------------------------------------------------------- */

    public function testRecommendLivesQuery()
    {
        $mutation = file_get_contents(__DIR__ . '/Live/Query/RecommendLivesQuery.gql');
        $header   = $this->getHeaders($this->user);
        $data     = array(
            "page" => 1,
        );
        $this->startGraphQL($mutation, $data, $header);
    }

    public function testRoomUsersQuery()
    {
        $mutation = file_get_contents(__DIR__ . '/Live/Query/RoomUsersQuery.gql');
        $header   = $this->getHeaders($this->user);

        $data = array(
            "room_id" => $this->liveRoom->id,
        );
        $this->startGraphQL($mutation, $data, $header);
    }

    protected function tearDown(): void
    {
        // $this->liveRoom->forceDelete();
        // $this->live->forceDelete();
        parent::tearDown();
    }
}
