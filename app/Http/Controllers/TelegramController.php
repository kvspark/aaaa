<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TelegramService;
use Illuminate\Support\Str;
use App\Models\MkfcBot;
use App\Models\Task;

use Illuminate\Support\Facades\Validator;

class TelegramController extends Controller
{
    protected $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function handleWebhook(Request $request)
    {
        $update = $request->all();

        if (isset($update['message']) && isset($update['message']['from'])) {
            // Get the Telegram ID and Username
            $chatId = $update['message']['from']['id']; // Telegram user ID
            $username = $update['message']['from']['username'] ?? "User_$chatId"; // Telegram username or fallback
    
            $text = $update['message']['text'];
    
            if (strpos($text, '/start') === 0) {
                // Extract referral code (if present) from /start command
                $referralCode = substr($text, 7);
    
                // Check if the user exists in the database
                $user = MkfcBot::where('telegram_id', $chatId)->first();
    
                if (!$user) {
                    // Register new user
                    $this->registerNewUser($chatId, $username, $referralCode);
                } else {
                    // User already exists, send welcome back message
                    $this->telegram->sendMessage($chatId, "Welcome back, $username!");
                }
            }
        }

        return response('OK', 200);
    }    

    private function registerNewUser($chatId, $username, $referralCode)
    {
        // Default earn value is 0 when registering
        $earns = 0;
        
        // Generate a new referral code for the user
        $myReferralCode = Str::random(10);
        
        // Initialize referred_by as null
        $referredBy = null;

        // Check if the user was referred by someone
        if (!empty($referralCode)) {
            // Find the user who owns the referral code
            $referrer = MkfcBot::where('my_referral_code', $referralCode)->first();
            if ($referrer) {
                $referredBy = $referrer->id;

                // Optionally, you could also award the referrer some tokens here
                $this->awardMkfcTokens($referrer, 0.001);  // Award referrer with 10 MKFC tokens
            }
        }

        // Create a new user record in the database
        MkfcBot::create([
            'telegram_id' => $chatId,
            'telegram_username' => $username,
            'earns' => $earns,
            'my_referral_code' => $myReferralCode,
            'referred_by' => $referredBy
        ]);

        // Send a welcome message
        $this->telegram->sendMessage($chatId, "👋 Welcome, $username  to the MK Flash Coin Tapping! Your referral link is https://t.me/mk_flash_coin_bot?start=$myReferralCode
 
 🔸 Here's how it works:
 1. **Tap** as fast as you can to score points!
 2. Earn **MKFC tokens** based on your performance.

 ✨ **Pro Tip:**Complete a task and earn 0.5 MKFC Tap Coin, Invite your friends using your unique referral link and earn 0.001 MKFC Tap Coin for every successful referral!

 Let the tapping begin! 🚀
        ");
    }

    private function awardMkfcTokens($user, $amount)
    {
        // Add MKFC tokens to the referrer's balance
        $user->earns += $amount;
        $user->save();

        // Optionally send a message to the referrer informing them about the bonus
        $this->telegram->sendMessage($user->telegram_id, "Congratulations! You've earned $amount MKFC tokens.");
    }

    public function updateEarns(Request $request)
    {
        $telegramId = $request->input('telegramId');
        $earn = $request->input('earn');

        // Find the user by Telegram ID and update their earn count
        $user = MkfcBot::where('telegram_id', $telegramId)->first();
        if ($user) {
            $user->earns = $earn; // Update the earn value
            $user->save();
            return response()->json(['success' => true]);
        }

        return response()->json(['status' => 404, 'error' => 'User not found']); 
    }

    public function userInfo($user_id)
    {
        $user = MkfcBot::where('telegram_id',$user_id)->first();
        if($user)
        {
            // return $user;
            return response()->json($user);
        }

        return response()->json(['status' => 404, 'message' => 'user not found']);
    }

    public function getReferredUsers($userId)
    {
        // Get all users referred by the user with the given Telegram ID
        $referredUsers = MkfcBot::where('referred_by', $userId)->get();

        return response()->json($referredUsers);
    }

    public function getTasks()
    {
        return Task::orderBy('id', 'desc')->get();
    }
    public function createTask(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'task_name' => 'required',
            'task_type' => 'required',
            'task_link' => 'required',
        ]);

        if($validator->fails())
        {
            return response()->json(['status' => 400, 'data' => $validator->errors()]) ;
        }

        $task = Task::create([
            'task_name' => $request->task_name,
            'task_type' => $request->task_type,
            'task_link' => $request->task_link,
        ]);
        if($task)
        {
            return response()->json(['status' => 201, 'message' => 'Task Added']);
        }
        return response()->json(['status' => 400, 'message' => 'Fail to add tasks']);
    }
    public function deleteTask($id)
    {
        $task = Task::destroy($id);
        if($task)
        {
            return response()->json(['status' => 200, 'message' => 'Task Deleted Successfully']);
        }
        return response()->json(['status' => 404, 'message' => 'No Task to delete']);
        
    }
}
