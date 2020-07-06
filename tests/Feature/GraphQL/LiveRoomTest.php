<?php

namespace Haxibiao\Live\Tests\Feature\GraphQL;

use App\User;
use Haxibiao\Base\GraphQLTestCase;
use Haxibiao\Live\LiveRoom;

class LiveRoomTest extends GraphQLTestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::find(2);
    }

    /* --------------------------------------------------------------------- */
    /* ------------------------------- Mutation ----------------------------- */
    /* --------------------------------------------------------------------- */

    public function testOpenLiveMutation()
    {
        $mutation = file_get_contents(__DIR__ . '/Live/Mutation/OpenLiveMutation.gql');
        $header   = $this->getHeaders($this->user);
        $data     = array(
            "title" => "testLive",
        );
        $this->startGraphQL($mutation, $data, $header);
    }

    public function testEnterLiveRoom()
    {
        $mutation = file_get_contents(__DIR__ . '/Live/Mutation/EnterLiveRoom.gql');
        $header   = $this->getHeaders($this->user);
        $id       = LiveRoom::max('id');
        $data     = array(
            "id" => $id,
        );
        $this->startGraphQL($mutation, $data, $header);
    }

    public function testLeaveLiveRoom()
    {
        $mutation = file_get_contents(__DIR__ . '/Live/Mutation/LeaveLiveRoom.gql');
        $header   = $this->getHeaders($this->user);
        $id       = LiveRoom::max('id');
        $data     = array(
            "roomid" => $id,
        );
        $this->startGraphQL($mutation, $data, $header);
    }

    public function testCommentLive()
    {
        $mutation = file_get_contents(__DIR__ . '/Live/Mutation/CommentLive.gql');
        $header   = $this->getHeaders($this->user);
        $id       = LiveRoom::max('id');
        $data     = array(
            "id"      => $id,
            "message" => "张志明真帅",
        );
        $this->startGraphQL($mutation, $data, $header);
    }

    public function testCloseLiveRoom()
    {
        $mutation = file_get_contents(__DIR__ . '/Live/Mutation/CloseLiveRoom.gql');
        $header   = $this->getHeaders($this->user);
        $live     = $this->user->live;
        $id       = $live->id;
        $data     = array(
            "roomid" => $id,
        );
        $this->startGraphQL($mutation, $data, $header);
    }

    public function testExceptionLiveReport()
    {
        $mutation = file_get_contents(__DIR__ . '/Live/Mutation/ExceptionLiveReport.gql');
        $header   = $this->getHeaders($this->user);
        $id       = LiveRoom::max('id');
        $data     = array(
            "roomid" => $id,
        );
        $this->startGraphQL($mutation, $data, $header);
    }

    // /* --------------------------------------------------------------------- */
    // /* ------------------------------- Query ----------------------------- */
    // /* --------------------------------------------------------------------- */

    public function testRecommendLiveRoom()
    {
        $mutation = file_get_contents(__DIR__ . '/Live/Query/RecommendLiveRoom.gql');
        $header   = $this->getHeaders($this->user);
        $data     = array(
            "page" => 1,
        );
        $this->startGraphQL($mutation, $data, $header);
    }

    public function testGetLiveRoomUsers()
    {
        $mutation = file_get_contents(__DIR__ . '/Live/Query/GetLiveRoomUsers.gql');
        $header   = $this->getHeaders($this->user);
        $id       = LiveRoom::max('id');
        $data     = array(
            "roomid" => $id,
        );
        $this->startGraphQL($mutation, $data, $header);
    }

    protected function tearDown(): void
    {
        // $this->user->liveRoom->forceDelete();
        //$this->user->forceDelete();
        parent::tearDown();
    }
}
