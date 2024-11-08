<?php
// In class-quest-manager.php
class CYOA_Quest_Manager {
    private $user_id;
    private $story_id;

    public function __construct($user_id, $story_id) {
        $this->user_id = $user_id;
        $this->story_id = $story_id;
    }

   // Update quest progress
   public function update_quest_progress($quest_id, $progress) {
        $user_state = get_user_meta($this->user_id, 'iasb_user_state', true) ?: array();
        if (!isset($user_state['quests'])) {
            $user_state['quests'] = array();
        }
        $user_state['quests'][$quest_id] = $progress;
        update_user_meta($this->user_id, 'iasb_user_state', $user_state);
    }

    // Get quest progress
    public function get_quest_progress($quest_id) {
        $quest_progress = $this->state['quests'] ?? [];

        if (is_array($quest_progress)) {
            if (is_string($quest_id)) {
                return isset($quest_progress[$quest_id]) ? $quest_progress[$quest_id] : 'Not started';
            } else {
                return 'Error: Quest ID must be a string';
            }
        } else {
            return 'Error: Quest progress is not an array';
        }
    }
    public function get_character_state() {
        $state = array(
            'inventory' => $this->state['inventory'] ?? [],
            'quests' => $this->get_all_quest_progress(),
            // Add other relevant state data here
        );
        //error_log('Character state in get_character_state: ' . print_r($state, true));
        return $state;
    }

    // Get all quest progress
    public function get_all_quest_progress() {
        $user_state = get_user_meta($this->user_id, 'iasb_user_state', true) ?: array();
        return isset($user_state['quests']) ? $user_state['quests'] : array();
    }




    public function start_quest($quest_id) {
        // Implementation for starting a new quest
    }

    public function complete_quest($quest_id) {
        // Implementation for completing a quest
    }

    public function get_active_quests() {
        // Implementation for retrieving all active quests
    }

    public function get_completed_quests() {
        // Implementation for retrieving all completed quests
    }

    // Add more methods as needed
}
