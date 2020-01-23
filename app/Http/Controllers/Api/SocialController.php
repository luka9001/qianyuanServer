<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2019/3/20
 * Time: 9:47 AM
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Likes;
use App\Models\Price;
use App\Models\SocialMessage;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class SocialController extends Controller
{
    public $successStatus = 200;
    public $failStatus = 500;

    //删除自己发布的信息，后续添加权限控制scope
    public function delMySocial(Request $request)
    {
        $id = request('id');
        //只能删除自己的
        SocialMessage::where([['user_id', $request->user()->id], ['id', $id]])->destroy();
        return response()->json(array('code' => 200));
    }

    //根据获取用户昵称
    public function getUserInfoById(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        $id = request('id');
        $user = User::find($id);
        $data['nickname'] = $user->name;
        $data['photo'] = $user->lifephoto;
        return response()->json(array('code' => 200, 'data' => $data));
    }

    //发布状态消息
    public function sendMessage(Request $request)
    {
        if ($request->user()->state != 1) {
            return response()->json(array('code' => '201'));
        }

        $this->validate($request, [
            'message' => 'required|string|max:100',
        ]);
        $user = $request->user();

        $socialMessage = new SocialMessage();
        $message = request('message');
        $socialMessage['message'] = $message;

        $fileStoragePath = public_path() . '/uploadFile/social/';
        $dateFolder = date('Y-m-d', time()); // 日期作为目录
        $folder = $fileStoragePath . $dateFolder;
        $files = $request->files;
        if (count($files) > 0) {
            if (!file_exists($folder) && !@mkdir($folder, 0775, true)) {
                return self::error(self::STATUS_BAD_REQUEST, '目录没有写入权限！');
            }
            $photos = array();

            foreach ($files as $file) {
                $clientName = $file->getClientOriginalName(); //初始名
                $entension = 'png';
                $newName = $dateFolder . '/' . Uuid::uuid4() . '.' . $entension;
                $file->move($folder, $newName);
                array_push($photos, $newName);
            }

            $socialMessage['photos'] = json_encode($photos);
        }

        $status = $user->social_messages()->save($socialMessage);

        /**
         * 首次发布动态心动币+1
         */
        $sCount = SocialMessage::where('user_id', $user->id)->count();
        if ($sCount === 1) {
            $price = Price::where('user_id', $user->id)->first();
            $price->coin = $price->coin + 1;
            $price->save();
        }
        if ($status) {
            return response()->json(array('code' => '200'));
        } else {
            return response()->json(array('code' => '201'));
        }
    }

    //获取社交圈信息
    public function getSocial(Request $request)
    {
        $socialMessage = SocialMessage::leftJoin('users', 'social_message.user_id', '=', 'users.id')->select('social_message.id', 'social_message.user_id', 'social_message.message', 'social_message.liked', 'social_message.created_at', 'social_message.photos', 'users.lifephoto', 'users.name', 'users.live', 'users.sex')->orderBy('id', 'desc')->paginate(10);
        $newUserData = User::where('state', '!=', 0)->select('id', 'lifephoto')->orderBy('id', 'desc')->paginate(6);
        foreach ($socialMessage as $item) {
            $item['likescount'] = Likes::where('social_message_id', '=', $item->id)->count();
            $item['commentcount'] = Comment::where('social_message_id', '=', $item->id)->count();
        }

        return response()->json(array('code' => 200, 'data' => $socialMessage, 'user' => $newUserData));
    }

    //获取社交圈信息
    public function getSocialLogin(Request $request)
    {
        $currentUserId = $request->user()->id;
        $socialMessage = SocialMessage::leftJoin('users', 'social_message.user_id', '=', 'users.id')->select('social_message.id', 'social_message.user_id', 'social_message.message', 'social_message.liked', 'social_message.created_at', 'social_message.photos', 'users.lifephoto', 'users.name', 'users.live', 'users.sex')->orderBy('id', 'desc')->paginate(10);
        $newUserData = User::where('state', '!=', 0)->select('id', 'lifephoto')->orderBy('id', 'desc')->paginate(6);
        foreach ($socialMessage as $item) {
            if ($item['user_id'] === $currentUserId) {
                $item['is_current_user'] = 1;
            }
            $item['likescount'] = Likes::where('social_message_id', '=', $item->id)->count();
            $item['commentcount'] = Comment::where('social_message_id', '=', $item->id)->count();
        }

        return response()->json(array('code' => 200, 'data' => $socialMessage, 'user' => $newUserData));
    }

    //获取我发布的社交圈信息
    public function getMySocial(Request $request)
    {
        $currentUserId = $request->user()->id;
        $id = request('id');
        if ($id != null) {
            $socialMessage = SocialMessage::leftJoin('users', 'social_message.user_id', '=', 'users.id')->where('social_message.user_id', $id)->select('social_message.id', 'social_message.user_id', 'social_message.message', 'social_message.liked', 'social_message.created_at', 'social_message.photos', 'users.lifephoto', 'users.name', 'users.live', 'users.sex')->orderBy('id', 'desc')->paginate(10);
            foreach ($socialMessage as $item) {
                if ($item['user_id'] === $currentUserId) {
                    $item['is_current_user'] = 1;
                }
                $item['likescount'] = Likes::where('social_message_id', '=', $item->id)->count();
                $item['commentcount'] = Comment::where('social_message_id', '=', $item->id)->count();
            }
        } else {
            $socialMessage = SocialMessage::leftJoin('users', 'social_message.user_id', '=', 'users.id')->where('social_message.user_id', $request->user()->id)->select('social_message.id', 'social_message.user_id', 'social_message.message', 'social_message.liked', 'social_message.created_at', 'social_message.photos', 'users.lifephoto', 'users.name', 'users.live', 'users.sex')->orderBy('id', 'desc')->paginate(10);
            foreach ($socialMessage as $item) {
                $item['likescount'] = Likes::where('social_message_id', '=', $item->id)->count();
                $item['commentcount'] = Comment::where('social_message_id', '=', $item->id)->count();
            }
        }
        return response()->json(array('code' => 200, 'data' => $socialMessage));
    }

    /**
     * 点赞
     */
    public function postLike(Request $request)
    {
        if ($request->user()->state != 1) {
            return response()->json(array('code' => 201));
        }
        // $this->validate($request, [
        //     'social_message_id' => 'required|string|max:2',
        // ]);

        $socialMessage_id = request('social_message_id');
        $to_user_id = request('to_user_id');

        $likes = new Likes();
        $likes->liked_uid = $request->user()->id;
        $likes->to_user_id = $to_user_id;
        $status = SocialMessage::find($socialMessage_id)->likes()->save($likes);

        if ($status) {
            return response()->json(array('code' => 200));
        } else {
            return response()->json(array('code' => 201));
        }
    }

    /**
     * 评论
     */
    public function postComment(Request $request)
    {
        if ($request->user()->state != 1) {
            return response()->json(array('code' => 201));
        }
        $socialMessage_id = request('social_message_id');
        $commentStr = request('comment');
        $to_user_id = request('to_user_id');

        $comment = new Comment();
        $comment->comment = $commentStr;
        $comment->from_user_id = $request->user()->id;
        $comment->to_user_id = $to_user_id;

        $return = SocialMessage::find($socialMessage_id)->comments()->save($comment);

        if ($return) {
            return response()->json(array('code' => 200));
        } else {
            return response()->json(array('code' => 201));
        }
    }

    /**
     * 删除自己发布的评论
     * @param Request $request
     */
    public function delMyComment(Request $request)
    {
        $currentUserId = $request->user()->id;
        $comment_id = request('comment_id');
        Comment::where([['id', $comment_id], ['from_user_id', $currentUserId]])->delete();
        return response()->json(array('code' => 200));
    }

    /**
     * 未登录 获取评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComments(Request $request)
    {
        $socialMessage_id = request('social_message_id');
        $to_user_id = request('to_user_id');
        DB::beginTransaction();
        $data = null;
        try {
            // $comments = SocialMessage::find($socialMessage_id)->comments();
            $comments = Comment::where('social_message_id', $socialMessage_id)->orderBy('id', 'desc')->paginate(10);
            // return response()->json(array('code' => 2000, 'data' => $comments));
            foreach ($comments as $key => $comment) {
                $user_id = $comment->from_user_id;
                $user = User::find($user_id);
                $comment->name = $user['name'];
                $comment->sex = $user['sex'];
                $comment->live = $user['live'];
                $comment->lifephoto = $user['lifephoto'];
            }
            $data = $comments;

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(array('code' => 201));
        }
        DB::commit();

        return response()->json(array('code' => 200, 'data' => $data));
    }

    /**
     * 登录后获取评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCommentsLogin(Request $request)
    {
        $currentUserId = $request->user()->id;
        $socialMessage_id = request('social_message_id');
        $to_user_id = request('to_user_id');
        DB::beginTransaction();
        $data = null;
        try {
            // $comments = SocialMessage::find($socialMessage_id)->comments();
            $comments = Comment::where('social_message_id', $socialMessage_id)->orderBy('id', 'desc')->paginate(10);
            // return response()->json(array('code' => 2000, 'data' => $comments));
            foreach ($comments as $key => $comment) {
                $user_id = $comment->from_user_id;
                if ($user_id === $currentUserId) {
                    $comment->is_current_user = 1;
                }
                $user = User::find($user_id);
                $comment->name = $user['name'];
                $comment->sex = $user['sex'];
                $comment->live = $user['live'];
                $comment->lifephoto = $user['lifephoto'];
            }
            $data = $comments;

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(array('code' => 201));
        }
        DB::commit();

        return response()->json(array('code' => 200, 'data' => $data));
    }

    public function getCommentsByUser(Request $request)
    {
        DB::beginTransaction();
        $data = null;
        try {
            $comments = Comment::where('to_user_id', $request->user()->id)->orWhere('from_user_id', $request->user()->id)->orderBy('id', 'desc')->paginate(10);
            Comment::where([['to_user_id', $request->user()->id], ['state', 0]])->update(['state' => 1]);
            foreach ($comments as $key => $comment) {
                $user_id = $comment->from_user_id;
                $user = User::find($user_id);
                $comment->name = $user['name'];
                $comment->sex = $user['sex'];
                $comment->live = $user['live'];
                $comment->lifephoto = $user['lifephoto'];

                $socialMessage = SocialMessage::find($comment->social_message_id);
                $comment->message = substr($socialMessage->message, 0, 6);
                // $comment->photos = $socialMessage->photos;
            }
            $data = $comments;

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(array('code' => 201));
        }
        DB::commit();

        return response()->json(array('code' => 200, 'data' => $data));
    }

    public function getThumbUpByUser(Request $request)
    {
        DB::beginTransaction();
        $data = null;
        try {
            $comments = Likes::where('to_user_id', $request->user()->id)->orWhere('liked_uid', $request->user()->id)->orderBy('id', 'desc')->paginate(10);
            Likes::where([['to_user_id', $request->user()->id], ['state', 0]])->update(['state' => 1]);
            foreach ($comments as $key => $comment) {
                $user_id = $comment->liked_uid;
                $user = User::find($user_id);
                $comment->name = $user['name'];
                $comment->sex = $user['sex'];
                $comment->live = $user['live'];
                $comment->lifephoto = $user['lifephoto'];

                $socialMessage = SocialMessage::find($comment->social_message_id);
                $comment->message = substr($socialMessage->message, 0, 6);
                // $comment->photos = $socialMessage->photos;
            }
            $data = $comments;

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(array('code' => 201));
        }
        DB::commit();

        return response()->json(array('code' => 200, 'data' => $data));
    }

    //给我的评论的数量 未按评论顺序来 已废弃
    public function getCommentsCount(Request $request)
    {
        DB::beginTransaction();
        $data = null;
        try {
            $users = DB::table('users')->join('comment', 'comment.from_user_id', '=', 'users.id')->where('comment.to_user_id', $request->user()->id)->where('comment.state', 0)->select('users.id', 'users.email', 'users.sex', 'users.live', 'users.name', 'users.lifephoto')->distinct('users.id')->paginate(10);
            foreach ($users as $key => $value) {
                $count = Comment::where([['from_user_id', $value->id], ['to_user_id', $request->user()->id], ['state', 0]])->count();
                $value->count = $count;
            }
            $data = $users;
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(array('code' => 201));
        }
        DB::commit();

        return response()->json(array('code' => 200, 'data' => $data));
    }
}
