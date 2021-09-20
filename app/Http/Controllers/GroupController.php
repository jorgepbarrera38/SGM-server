<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:groups.index')->only('index');
        $this->middleware('has.permission:groups.create')->only('store');
        $this->middleware('has.permission:groups.edit')->only('update');
        $this->middleware('has.permission:groups.destroy')->only('destroy');
        $this->middleware('has.permission:groups.authorization')->only(['getGroupWithUsersToAuthorize', 'authorizeUsersInGroup']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Group::name(request('name'))->orderBy('name')->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:30|unique:groups,name'
        ], [], [
            'name' => 'Nombre'
        ]);

        Group::create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function show(Group $group)
    {
        return $group;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Group $group)
    {
        $data = $request->validate([
            'name' => 'required|max:30|unique:groups,name,' . $group->id
        ], [], [
            'name' => 'Nombre'
        ]);

        $group->update($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        $group->delete();
    }
    
    public function getGroupWithUsersToAuthorize($group)
    {
        $users = User::orderBy('names', 'asc')->get();
        $group = Group::with('users')->where('id', $group)->first();

        return [
            'users' => $users,
            'group' => $group
        ];
    }

    public function authorizeUsersInGroup(Group $group, Request $request)
    {
        $data = $request->validate([
            'users' => 'nullable|array',
            'users.*.id' => 'required|exists:users,id'
        ]);

        $usersId = array_map(function($user) {
            return $user['id'];
        }, $data['users']);

        if (count($data['users'])) {
            $group->users()->sync($usersId);
        } else {
            $group->users()->detach();
        }

    }
}
