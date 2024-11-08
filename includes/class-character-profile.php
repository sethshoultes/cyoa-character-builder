<?php
// In class-character-profile.php
class CYOA_Character_Profile {
    private $inventory_manager;
    private $state_manager;
    private $quest_manager;
    private $user_id;
    private $story_id;
    private $character_data = [];

    public function __construct($user_id, $story_id, $character_id) {
        $this->user_id = $user_id;
        $this->story_id = $story_id;
        $this->inventory_manager = new CYOA_Inventory_Manager($user_id, $story_id);
        $this->state_manager = new CYOA_State_Manager($user_id, $story_id, $character_id);
        $this->quest_manager = new CYOA_Quest_Manager($user_id, $story_id);
    }

       // Get character attribute
    public function get_character_attribute($name) {
        $name = strtolower($name);
        return $this->character_data['Attributes'][$name] ?? '';
    }

    //  Update character attributes
    public function update_character_attribute($attribute, $value) {
        $attribute = strtolower($attribute);
        $this->character_data['Attributes'][$attribute] = $value;
        update_user_meta($this->user_id, 'adventure_game_character', $this->character_data);
    }
   

    public function get_inventory_manager() {
        return $this->inventory_manager;
    }

    public function get_state_manager() {
        return $this->state_manager;
    }

    public function get_quest_manager() {
        return $this->quest_manager;
    }



    // Add more methods as needed
}