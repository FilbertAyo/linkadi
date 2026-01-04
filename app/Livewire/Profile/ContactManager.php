<?php

namespace App\Livewire\Profile;

use App\Models\ProfileContact;
use Livewire\Component;

class ContactManager extends Component
{
    public $profileId;
    public $type; // 'phone' or 'email'
    public $contacts = [];
    public $newContact = [
        'value' => '',
        'category' => 'main',
        'custom_label' => '',
        'is_primary' => false,
        'is_public' => true,
    ];
    public $showAddForm = false;

    protected function rules()
    {
        $rules = [
            'newContact.value' => $this->type === 'phone' ? ['required', 'string', 'max:20'] : ['required', 'email', 'max:255'],
            'newContact.category' => ['required', 'in:main,work,home,mobile,custom'],
            'newContact.custom_label' => ['nullable', 'string', 'max:255'],
            'newContact.is_primary' => ['boolean'],
            'newContact.is_public' => ['boolean'],
        ];

        if ($this->newContact['category'] === 'custom') {
            $rules['newContact.custom_label'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function mount($profileId, $type)
    {
        $this->profileId = $profileId;
        $this->type = $type;
        $this->loadContacts();
    }

    public function loadContacts()
    {
        $this->contacts = ProfileContact::where('profile_id', $this->profileId)
            ->where('type', $this->type)
            ->orderBy('is_primary', 'desc')
            ->orderBy('order')
            ->get()
            ->toArray();
    }

    public function addContact()
    {
        $this->validate();

        // If setting as primary, unset other primary contacts of this type
        if ($this->newContact['is_primary']) {
            ProfileContact::where('profile_id', $this->profileId)
                ->where('type', $this->type)
                ->update(['is_primary' => false]);
        }

        ProfileContact::create([
            'profile_id' => $this->profileId,
            'type' => $this->type,
            'value' => $this->newContact['value'],
            'category' => $this->newContact['category'],
            'custom_label' => $this->newContact['custom_label'],
            'is_primary' => $this->newContact['is_primary'],
            'is_public' => $this->newContact['is_public'],
            'order' => count($this->contacts),
        ]);

        $this->reset('newContact', 'showAddForm');
        $this->loadContacts();
        $this->dispatch('contacts-updated');
    }

    public function deleteContact($contactId)
    {
        $contact = ProfileContact::find($contactId);
        
        if ($contact && $contact->profile_id == $this->profileId) {
            $contact->delete();
            $this->loadContacts();
            $this->dispatch('contacts-updated');
        }
    }

    public function setPrimary($contactId)
    {
        // Unset all primary for this type
        ProfileContact::where('profile_id', $this->profileId)
            ->where('type', $this->type)
            ->update(['is_primary' => false]);

        // Set this one as primary
        ProfileContact::where('id', $contactId)->update(['is_primary' => true]);

        $this->loadContacts();
        $this->dispatch('contacts-updated');
    }

    public function updateOrder($orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            ProfileContact::where('id', $id)->update(['order' => $index]);
        }

        $this->loadContacts();
    }

    public function render()
    {
        return view('livewire.profile.contact-manager');
    }
}
