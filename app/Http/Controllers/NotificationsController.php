<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationsResource;
use App\Http\Resources\NotificationsSettingsResource;
use App\Models\Customer;
use App\Models\Seller;
use App\Settings\AppSettings;
use Illuminate\Http\Request;
use Lang;

class NotificationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('not');
    }

    public function getNotifications(Request $request, AppSettings $settings)
    {
        $notifications = $request->user()->notifications()->paginate(10);

        return $this->ApiResponseFormatted(200, NotificationsResource::collection($notifications), Lang::get('api.success'), $settings, $request);
    }

    public function deleteNotification(Request $request, AppSettings $settings,$id)
    {
        $notification = $request->user()->notifications()->where('id',$id)->first();
        if($notification){
            $notification->delete();
            return $this->ApiResponseFormatted(200, [], Lang::get('api.success'), $settings, $request);
        }
        return $this->ApiResponseFormatted(404, [], Lang::get('api.record_not_found'), $settings, $request);

    }

    public function markAllAsRead(Request $request, AppSettings $settings)
    {
        $request->user()->unreadNotifications->markAsRead();
        return $this->ApiResponseFormatted(200, [], Lang::get('api.success'), $settings, $request);
    }

    public function markAsRead(Request $request, AppSettings $settings, $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
            return $this->ApiResponseFormatted(200, [], Lang::get('api.success'), $settings, $request);
        }
        return $this->ApiResponseFormatted(404, [], Lang::get('api.record_not_found'), $settings, $request);
    }

    public function getUnreadCount(Request $request, AppSettings $settings)
    {
        $count = $request->user()->unreadNotifications()->count();
        return $this->ApiResponseFormatted(200, ['count' => $count], Lang::get('api.success'), $settings, $request);
    }

    public function getCustomerNotificationsSettings(Request $request, AppSettings $settings)
    {
        $customer = Customer::find($request->user()->id);
        $notificationSettings = $customer->notificationSettings;
        return $this->ApiResponseFormatted(200, NotificationsSettingsResource::make($notificationSettings), Lang::get('api.success'), $settings, $request);

    }

    public function updateCustomerNotificationsSettings(Request $request, AppSettings $settings)
    {
        $customer = Customer::find($request->user()->id);
        $notificationSettings = $customer->notificationSettings;
        $notificationSettings->update($request->all());
        return $this->ApiResponseFormatted(200, NotificationsSettingsResource::make($notificationSettings), Lang::get('api.success'), $settings, $request);

    }

     public function not(Request $request , AppSettings $settings,$id)
     {
         $seller = Seller::find($id);

         $notifications = $seller->notifications()->get();

            return $this->ApiResponseFormatted(200, $notifications, Lang::get('api.success'), $settings, $request);

     }
}
