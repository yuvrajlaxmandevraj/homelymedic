<?php

namespace App\Models;

use CodeIgniter\Model;

class ticket_message_modal extends Model
{

    protected $table = 'ticket_messages';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['user_type', 'user_id', 'ticket_id', 'message', 'attachments'];
}
