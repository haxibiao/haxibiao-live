<?php

// namespace Haxibiao\Live\Tests\Feature\GraphQL;

// use Haxibiao\Base\GraphQLTestCase;
// use Haxibiao\Live\LiveRoom;

// class LiveTest extends GraphQLTestCase
// {
//     protected $user;

//     protected $liveRoom;

//     protected function setUp(): void
//     {
//         parent::setUp();
//         $this->user = $this->getRandomUser();

//         $this->liveRoom = factory(LiveRoom::class)->create([
//             //用户ID
//             'anchor_id'   => $this->user->id,
//             //流名称
//             'stream_name' => 'u:' . $this->user->id,
//         ]);
//     }

//     /* --------------------------------------------------------------------- */
//     /* ------------------------------- Mutation ---------------------------- */
//     /* --------------------------------------------------------------------- */

//     //直播间发表评论
//     public function testCommentLiveRoomMutation()
//     {
//         $gql       = file_get_contents(__DIR__ . '/Live/commentLive.gql');
//         $variables = [
//             'id'      => $this->liveRoom->id,
//             'message' => '张志明真帅',
//         ];
//         $this->startGraphQL($gql, $variables, $this->getHeaders($this->user));
//     }

//     //加入直播间
//     public function testJoinLiveRoomMutation()
//     {
//         $audience = $this->getRandomUser();

//         $gql       = file_get_contents(__DIR__ . '/Live/enterLiveRoom.gql');
//         $variables = [
//             'id' => $this->liveRoom->id,
//         ];
//         $this->startGraphQL($gql, $variables, $this->getHeaders($audience));
//     }

//     //主播下播
//     public function testCloseLiveRoomMutation()
//     {
//         $gql       = file_get_contents(__DIR__ . '/Live/closeLiveRoom.gql');
//         $variables = [
//             'roomid' => $this->liveRoom->id,
//         ];
//         $this->startGraphQL($gql, $variables, $this->getHeaders($this->user));
//     }

//     //主播直播间异常下播
//     public function testExceptionLiveRoomMutation()
//     {
//         $gql       = file_get_contents(__DIR__ . '/Live/exceptionLiveReport.gql');
//         $variables = [
//             'roomid' => $this->liveRoom->id,
//         ];
//         $this->startGraphQL($gql, $variables, $this->getHeaders($this->user));
//     }

//     //获取直播推流地址
//     public function testCreateLiveRoomMutation()
//     {
//         $gql       = file_get_contents(__DIR__ . '/Live/getLivePushURL.gql');
//         $variables = [
//             'title' => $this->liveRoom->title,
//         ];
//         $this->startGraphQL($gql, $variables, $this->getHeaders($this->user));
//     }

//     //用户离开直播间
//     public function testLeaveLiveRoomMutation()
//     {
//         //用户进入直播间
//         $audience = $this->getRandomUser();

//         $gql       = file_get_contents(__DIR__ . '/Live/enterLiveRoom.gql');
//         $variables = [
//             'id' => $this->liveRoom->id,
//         ];
//         $this->startGraphQL($gql, $variables, $this->getHeaders($audience));

//         //用户离开直播间
//         $gql       = file_get_contents(__DIR__ . '/Live/leaveLiveRoom.gql');
//         $variables = [
//             'roomid' => $this->liveRoom->id,
//         ];
//         $this->startGraphQL($gql, $variables, $this->getHeaders($audience));
//     }

//     /* --------------------------------------------------------------------- */
//     /* ------------------------------- Query ------------------------------- */
//     /* --------------------------------------------------------------------- */

//     //直播列表
//     public function testRecommendLiveRoomQuery()
//     {
//         $gql       = file_get_contents(__DIR__ . '/Live/recommendLiveRoomQuery.gql');
//         $variables = [
//             'page' => 1,
//         ];
//         $this->startGraphQL($gql, $variables, $this->getHeaders($this->user));
//     }

//     //在线观众列表
//     public function testGetLiveRoomUsersQuery()
//     {
//         $gql       = file_get_contents(__DIR__ . '/Live/getOnlinePeopleQuery.gql');
//         $variables = [
//             'roomid' => $this->liveRoom->id,
//         ];
//         $this->startGraphQL($gql, $variables, $this->getHeaders($this->user));
//     }

//     protected function tearDown(): void
//     {
//         $this->user->forceDelete();
//         $this->liveRoom->forceDelete();
//         parent::tearDown();
//     }
// }
