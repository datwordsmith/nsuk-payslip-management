<?php

namespace App\Http\Livewire\Admin\User;

use App\Models\User;
use App\Notifications\NewUserAlert;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $user_id, $name, $email, $password, $confirm_password, $isAdmin, $status = 1;
    public $search;

    public function rules()
    {
        return [
            'name' => 'required|string|min:2',
            'email' => 'required|email|unique:users',
            //'email' => 'required|email|unique:users,email,' . $this->user->id,
            //'password' => 'required|string|min:8',
            //'confirm_password' => 'required|same:password',
            'status' => 'required|integer|between:0,1',
        ];
    }

    public function mount(User $user)
    {
        $this->user = $user;
        $this->admin = Auth::user();


    }

    public function resetInput() {
        $this->name = NULL;
        $this->email = NULL;
        /* $this->password = NULL;
        $this->confirm_password = NULL; */
    }

    public function storeUser()
    {
        $validatedData = $this->validate();

        $userPassword = preg_replace("/[^a-zA-Z0-9]/", '', Str::random(8));

        $user = new User([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($userPassword),
        ]);
        $user->save();

        $user->notify(new NewUserAlert($userPassword));

        session()->flash('message', 'User Added Successfully');

        $this->dispatchBrowserEvent('close-modal');
        $this->resetInput();
    }

    public function editUser(int $user_id)
    {

        $user = User::FindOrFail($user_id);
        $this->user_id = $user_id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->status = $user->status;
        $this->isAdmin = $user->isAdmin;

    }

    public function updateUser()
    {

        $status = $this->status ? 1 : 0;

        User::findOrFail($this->user_id)->update([
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
        ]);

        session()->flash('message', 'User Updated Successfully');
        $this->dispatchBrowserEvent('close-modal');
        $this->resetInput();
    }

    public function deleteUser($user_id)
    {
        $this->user_id = $user_id;
    }

    public function destroyUser()
    {
        try {
            $user = User::FindOrFail($this->user_id);
            $user->delete();
            session()->flash('message', 'User deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1451) { // check if error is foreign key constraint violation
                session()->flash('error', 'Cannot delete user because it is referenced in another module.');
            } else {
                session()->flash('error', 'An error occurred while deleting the user.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while deleting the user.');
        }
        $this->dispatchBrowserEvent('close-modal');
        $this->resetInput();
    }

    public function closeModal() {
        $this->resetInput();
    }

    public function openModal() {
        $this->resetInput();
    }

    public function render()
    {
        $users = User::where(function($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                })
                ->where('email', '<>', 'emeka.daniels@gmail.com')
                ->orderBy('name', 'ASC')
                ->paginate(10);

        return view('livewire.admin.user.index', ['users' => $users])->extends('layouts.admin')->section('content');
    }
}
