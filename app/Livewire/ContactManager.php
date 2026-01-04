<?php

namespace App\Livewire;

use App\Models\ProfileContact;
use Livewire\Component;

class ContactManager extends Component
{
    public $profileId;
    public $contacts = [];
    public $showAddForm = false;
    public $editingId = null;
    
    // Form fields
    public $type = 'phone';
    public $value = '';
    public $category = 'main';
    public $custom_label = '';
    public $is_primary = false;
    public $is_public = true;

    protected $rules = [
        'type' => 'required|in:phone,email',
        'value' => 'required|string|max:255',
        'category' => 'required|in:main,work,home,mobile,custom',
        'custom_label' => 'nullable|required_if:category,custom|string|max:100',
        'is_primary' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function mount($profileId)
    {
        $this->profileId = $profileId;
        $this->loadContacts();
    }

    public function loadContacts()
    {
        $this->contacts = ProfileContact::where('profile_id', $this->profileId)
            ->orderBy('type')
            ->orderBy('is_primary', 'desc')
            ->orderBy('order')
            ->get()
            ->toArray();
    }

    public function addContact()
    {
        $this->showAddForm = true;
        $this->resetForm();
    }

    public function cancelAdd()
    {
        $this->showAddForm = false;
        $this->resetForm();
    }

    public function saveContact()
    {
        $this->validate();

        // If setting as primary, unset other primaries of same type
        if ($this->is_primary) {
            ProfileContact::where('profile_id', $this->profileId)
                ->where('type', $this->type)
                ->update(['is_primary' => false]);
        }

        // Get max order for this type
        $maxOrder = ProfileContact::where('profile_id', $this->profileId)
            ->where('type', $this->type)
            ->max('order') ?? -1;

        if ($this->editingId) {
            // Update existing
            $contact = ProfileContact::find($this->editingId);
            $contact->update([
                'value' => $this->value,
                'category' => $this->category,
                'custom_label' => $this->category === 'custom' ? $this->custom_label : null,
                'is_primary' => $this->is_primary,
                'is_public' => $this->is_public,
            ]);
        } else {
            // Create new
            ProfileContact::create([
                'profile_id' => $this->profileId,
                'type' => $this->type,
                'value' => $this->value,
                'category' => $this->category,
                'custom_label' => $this->category === 'custom' ? $this->custom_label : null,
                'is_primary' => $this->is_primary,
                'is_public' => $this->is_public,
                'order' => $maxOrder + 1,
            ]);
        }

        $this->loadContacts();
        $this->cancelAdd();
        $this->dispatch('contact-saved');
    }

    public function editContact($id)
    {
        $contact = ProfileContact::find($id);
        
        $this->editingId = $id;
        $this->type = $contact->type;
        $this->value = $contact->value;
        $this->category = $contact->category;
        $this->custom_label = $contact->custom_label;
        $this->is_primary = $contact->is_primary;
        $this->is_public = $contact->is_public;
        $this->showAddForm = true;
    }

    public function deleteContact($id)
    {
        ProfileContact::find($id)->delete();
        $this->loadContacts();
    }

    public function setPrimary($id)
    {
        $contact = ProfileContact::find($id);
        
        // Unset other primaries of same type
        ProfileContact::where('profile_id', $this->profileId)
            ->where('type', $contact->type)
            ->update(['is_primary' => false]);
        
        // Set this as primary
        $contact->update(['is_primary' => true]);
        
        $this->loadContacts();
    }

    public function moveUp($id)
    {
        $contact = ProfileContact::find($id);
        $prevContact = ProfileContact::where('profile_id', $this->profileId)
            ->where('type', $contact->type)
            ->where('order', '<', $contact->order)
            ->orderBy('order', 'desc')
            ->first();
        
        if ($prevContact) {
            $tempOrder = $contact->order;
            $contact->update(['order' => $prevContact->order]);
            $prevContact->update(['order' => $tempOrder]);
            $this->loadContacts();
        }
    }

    public function moveDown($id)
    {
        $contact = ProfileContact::find($id);
        $nextContact = ProfileContact::where('profile_id', $this->profileId)
            ->where('type', $contact->type)
            ->where('order', '>', $contact->order)
            ->orderBy('order', 'asc')
            ->first();
        
        if ($nextContact) {
            $tempOrder = $contact->order;
            $contact->update(['order' => $nextContact->order]);
            $nextContact->update(['order' => $tempOrder]);
            $this->loadContacts();
        }
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->type = 'phone';
        $this->value = '';
        $this->category = 'main';
        $this->custom_label = '';
        $this->is_primary = false;
        $this->is_public = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.contact-manager');
    }
}
