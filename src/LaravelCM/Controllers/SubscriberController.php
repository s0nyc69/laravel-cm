<?php

namespace Flobbos\LaravelCM\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Flobbos\LaravelCM\Contracts\SubscriberContract;
use Flobbos\LaravelCM\Contracts\ListContract;
use Exception;

class SubscriberController extends Controller
{

    protected $subscribers;

    public function __construct(SubscriberContract $subscribers)
    {
        $this->subscribers = $subscribers;
    }

    /**
     * Show subscriber listing
     * @param Request $request
     * @param ListContract $lists
     * @return type
     */
    public function index(Request $request, ListContract $lists)
    {
        try {
            //Check if list ID was selected
            if ($request->has('listID')) {
                $this->subscribers->setListID($request->get('listID'));
                $lists->setListID($request->get('listID'));
            }
            //Get lists
            $email_lists = $lists->get();
            //Get subscribed 
            $subscribed = $this->subscribers->getActive($request->get('subscribers') ?: 1, 'subscribers');
            //Get unconfirmed subscriptions
            $unconfirmed = $this->subscribers->getUnconfirmed($request->get('unconfirmed') ?: 1, 'unconfirmed');
            //Get unsubscribed
            $unsubscribed = $this->subscribers->getUnsubscribed($request->get('unsubscribed') ?: 1, 'unsubscribed');
            //Get all bounced subscriptions
            $bounced = $this->subscribers->getBounced($request->get('bounced') ?: 1, 'bounced');
        } catch (Exception $ex) {
            return view('laravel-cm::subscribers.index')->with([
                'subscribed' => collect([]),
                'unconfirmed' => collect([]),
                'unsubscribed' => collect([]),
                'bounced' => collect([]),
                'lists' => $email_lists ?? collect([]),
            ])->withErrors($ex->getMessage() . ' -- ' . $ex->getFile());
        }
        return view('laravel-cm::subscribers.index')->with([
            'subscribed' => $subscribed,
            'unconfirmed' => $unconfirmed,
            'unsubscribed' => $unsubscribed,
            'bounced' => $bounced,
            'lists' => $email_lists
        ]);
    }

    /**
     * Show details for specific subscriber
     * @param type $email
     * @return type
     */
    public function showDetails($email, Request $request)
    {
        try {
            $subscriber = $this->subscribers->setListID($request->get('listID'))->getDetails($email);

            return view('laravel-cm::subscribers.show')->with([
                'subscriber' => $subscriber->get('body')
            ]);
        } catch (Exception $ex) {
            return redirect()->back()->withErrors($ex->getMessage());
        }
    }

    public function edit($email, Request $request, ListContract $lists)
    {
        try {
            $subscriber = $this->subscribers->setListID($request->get('listID'))->getDetails($email);
            $email_lists = $lists->get();
            return view('laravel-cm::subscribers.edit')->with([
                'subscriber' => $subscriber,
                'email_lists' => $email_lists
            ]);
        } catch (Exception $ex) {
            return redirect()->back()->withErrors($ex->getMessage());
        }
    }

    public function update($email, Request $request)
    {
        try {
            $this->subscribers->setListID($request->get('listID'))->update($email, $request->all());
            return redirect()->route('laravel-cm::subscribers.index', ['listID' => $request->get('listID')]);
        } catch (Exception $ex) {
            return redirect()->back()->withErrors($ex->getMessage());
        }
    }

    /**
     * Resubscribe a person to the newsletter
     * @param type $email
     * @return type
     * @throws Exception
     */
    public function resubscribe($email, Request $request)
    {
        try {
            if (!$this->subscribers->setListID($request->get('listID'))->resubscribe($email)) {
                throw new Exception(trans('laravel-cm::subscribers.email_not_added'));
            }
            return redirect()->route('laravel-cm::subscribers.index')->withMessage(trans('laravel-cm::subscribers.email_added'));
        } catch (Exception $ex) {
            return redirect()->back()->withInput()->withErrors($ex->getMessage());
        }
    }

    public function unsubscribe($email, Request $request)
    {
        try {
            $this->subscribers->setListID($request->get('listID'))->remove($email);
            return redirect()->route('laravel-cm::subscribers.index', ['listID' => $request->get('listID')])->withMessage(trans('laravel-cm::crud.record_deleted'));
        } catch (Exception $ex) {
            return redirect()->route('laravel-cm::subscribers.index', ['listID' => $request->get('listID')])->withErrors($ex->getMessage());
        }
    }

    /********* Imports *************/
    /**
     * Show the file/list select for importing
     * @param ListContract $lists
     * @return type
     */
    public function showImport(ListContract $lists)
    {
        try {
            return view('laravel-cm::subscribers.import')->withLists($lists->get());
        } catch (Exception $ex) {
            return redirect()->back()->withErrors($ex->getMessage());
        }
    }

    /**
     * Handle the request and import the subscribers to the given list
     * @param Request $request
     * @return type
     */
    public function import(Request $request)
    {
        //dd($request->all());
        if (!$request->hasFile('excel')) {
            return redirect()->back()->withErrors(trans('laravel-cm::subscribers.no_xls_found'));
        }
        //Handle file upload
        try {
            $result = $this->subscribers->import($request);
            return redirect()->back()->with([
                'result' => $result
            ])->withMessage(trans('laravel-cm::subscribers.xls_imported'));
        } catch (Exception $ex) {
            return redirect()->back()->withErrors($ex->getMessage());
        }
    }
}
