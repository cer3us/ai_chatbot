<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->create_telegram_bots();

        // Add main tables

        $this->create_telegram_updates();
        $this->create_telegram_messages();
        $this->create_telegram_business_connections();
        $this->create_telegram_business_messages_deleted();
        $this->create_telegram_message_reaction_updated();
        $this->create_telegram_message_reaction_count_updated();
        $this->create_telegram_inline_queries();
        $this->create_telegram_chosen_inline_results();
        $this->create_telegram_callback_queries();
        $this->create_telegram_shipping_queries();
        $this->create_telegram_pre_checkout_queries();
        $this->create_telegram_paid_media_purchased();
        $this->create_telegram_polls();
        $this->create_telegram_poll_answers();
        $this->create_telegram_chat_member_updated();
        $this->create_telegram_chat_join_requests();
        $this->create_telegram_chat_boost_updated();
        $this->create_telegram_chat_boost_removed();

        $this->create_telegram_users();
        $this->create_telegram_chats();

        $this->create_telegram_conversations();

        // Add foreign keys

        $this->add_foreign_keys();
    }

    private function add_foreign_keys()
    {
        $this->add_foreign_keys_to_telegram_updates();
        $this->add_foreign_keys_to_telegram_messages();
        $this->add_foreign_keys_to_telegram_business_connections();
        $this->add_foreign_keys_to_telegram_business_messages_deleted();
        $this->add_foreign_keys_to_telegram_message_reaction_updated();
        $this->add_foreign_keys_to_telegram_message_reaction_count_updated();
        $this->add_foreign_keys_to_telegram_inline_queries();
        $this->add_foreign_keys_to_telegram_chosen_inline_results();
        $this->add_foreign_keys_to_telegram_callback_queries();
        $this->add_foreign_keys_to_telegram_shipping_queries();
        $this->add_foreign_keys_to_telegram_pre_checkout_queries();
        $this->add_foreign_keys_to_telegram_paid_media_purchased();
        $this->add_foreign_keys_to_telegram_poll_answers();
        $this->add_foreign_keys_to_telegram_chat_member_updated();
        $this->add_foreign_keys_to_telegram_chat_join_requests();
        $this->add_foreign_keys_to_telegram_chat_boost_updated();
        $this->add_foreign_keys_to_telegram_chat_boost_removed();
        $this->add_foreign_keys_to_telegram_chats();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_bots');
        Schema::dropIfExists('telegram_updates');
        Schema::dropIfExists('telegram_messages');
        Schema::dropIfExists('telegram_business_connections');
        Schema::dropIfExists('telegram_business_message_deleted');
        Schema::dropIfExists('telegram_message_reaction_updated');
        Schema::dropIfExists('telegram_message_reaction_count_updated');
        Schema::dropIfExists('telegram_inline_queries');
        Schema::dropIfExists('telegram_chosen_inline_results');
        Schema::dropIfExists('telegram_callback_queries');
        Schema::dropIfExists('telegram_shipping_queries');
        Schema::dropIfExists('telegram_pre_checkout_queries');
        Schema::dropIfExists('telegram_paid_media_purchased');
        Schema::dropIfExists('telegram_polls');
        Schema::dropIfExists('telegram_poll_answers');
        Schema::dropIfExists('telegram_chat_member_updated');
        Schema::dropIfExists('telegram_chat_join_requests');
        Schema::dropIfExists('telegram_chat_boost_updated');
        Schema::dropIfExists('telegram_chat_boost_removed');
        Schema::dropIfExists('telegram_users');
        Schema::dropIfExists('telegram_chats');
        Schema::dropIfExists('telegram_conversations');
    }

    private function create_telegram_bots()
    {
        Schema::create('telegram_bots', function (Blueprint $table) {
            $table->id('telegram_bot_id');

            $table->string('name')->nullable();
            $table->string('token')->nullable();
            $table->string('first_name')->nullable();
            $table->string('username')->nullable();
            $table->boolean('can_join_groups')->nullable();
            $table->boolean('can_read_all_group_messages')->nullable();
            $table->boolean('supports_inline_queries')->nullable();
            $table->boolean('can_connect_to_business')->nullable();
            $table->boolean('has_main_web_app')->nullable();

            $table->timestamps();
        });
    }

    private function create_telegram_updates(): void
    {
        Schema::create('telegram_updates', function (Blueprint $table) {
            $table->id('telegram_update_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->bigInteger('update_id')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_updates(): void
    {
        Schema::table('telegram_updates', function (Blueprint $table) {
            $table->after('update_id', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Message::class, 'telegram_message_id')->nullable()->constrained('telegram_messages');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Message::class, 'telegram_edited_message_id')->nullable()->constrained('telegram_messages');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Message::class, 'telegram_channel_post_id')->nullable()->constrained('telegram_messages');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Message::class, 'telegram_edited_channel_post_id')->nullable()->constrained('telegram_messages');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Business\BusinessConnection::class, 'telegram_business_connection_id')->nullable()->constrained('telegram_business_connections');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Message::class, 'telegram_business_message_id')->nullable()->constrained('telegram_messages');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Message::class, 'telegram_edited_business_message_id')->nullable()->constrained('telegram_messages');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Business\BusinessMessagesDeleted::class, 'telegram_deleted_business_messages_id')->nullable()->constrained('telegram_business_messages_deleted');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\ReactionType\MessageReactionUpdated::class, 'telegram_message_reaction_id')->nullable()->constrained('telegram_message_reaction_updated');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\ReactionType\MessageReactionCountUpdated::class, 'telegram_message_reaction_count_id')->nullable()->constrained('telegram_message_reaction_count_updated');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\InlineQuery::class, 'telegram_inline_query_id')->nullable()->constrained('telegram_inline_queries');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\ChosenInlineResult::class, 'telegram_chosen_inline_result_id')->nullable()->constrained('telegram_chosen_inline_results');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\CallbackQuery::class, 'telegram_callback_query_id')->nullable()->constrained('telegram_callback_queries');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Payments\ShippingQuery::class, 'telegram_shipping_query_id')->nullable()->constrained('telegram_shipping_queries');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Payments\PreCheckoutQuery::class, 'telegram_pre_checkout_query_id')->nullable()->constrained('telegram_pre_checkout_queries');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\PaidMedia\PaidMediaPurchased::class, 'telegram_paid_media_purchased_id')->nullable()->constrained('telegram_paid_media_purchased');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Attachments\Poll::class, 'telegram_poll_id')->nullable()->constrained('telegram_polls');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Attachments\PollAnswer::class, 'telegram_poll_answer_id')->nullable()->constrained('telegram_poll_answers');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\ChatMember\ChatMemberUpdated::class, 'telegram_my_chat_member_id')->nullable()->constrained('telegram_chat_member_updated');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\ChatMember\ChatMemberUpdated::class, 'telegram_chat_member_id')->nullable()->constrained('telegram_chat_member_updated');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\ChatMember\ChatJoinRequest::class, 'telegram_chat_join_request_id')->nullable()->constrained('telegram_chat_join_requests');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\ChatBoost\ChatBoostUpdated::class, 'telegram_chat_boost_id')->nullable()->constrained('telegram_chat_boost_updated');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\ChatBoost\ChatBoostRemoved::class, 'telegram_chat_boost_removed_id')->nullable()->constrained('telegram_chat_boost_removed');
            });
        });
    }

    private function create_telegram_messages(): void
    {
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id('telegram_message_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->bigInteger('message_id')->nullable();
            $table->bigInteger('message_thread_id')->nullable();
            $table->bigInteger('sender_boost_count')->nullable();
            $table->dateTime('date');
            $table->bigInteger('business_connection_id')->nullable();
            $table->json('forward_origin')->nullable();
            $table->boolean('is_topic_message')->nullable();
            $table->boolean('is_automatic_forward')->nullable();
            $table->json('external_reply')->nullable();
            $table->json('quote')->nullable();
            $table->json('reply_to_story')->nullable();
            $table->timestamp('edit_date')->nullable();
            $table->boolean('has_protected_content')->nullable();
            $table->boolean('is_from_offline')->nullable();
            $table->string('media_group_id')->nullable();
            $table->string('author_signature')->nullable();
            $table->text('text')->nullable();
            $table->json('entities')->nullable();
            $table->json('link_preview_options')->nullable();
            $table->bigInteger('effect_id')->nullable();
            $table->json('animation')->nullable();
            $table->json('audio')->nullable();
            $table->json('document')->nullable();
            $table->json('paid_media')->nullable();
            $table->json('photo')->nullable();
            $table->json('sticker')->nullable();
            $table->json('story')->nullable();
            $table->json('video')->nullable();
            $table->json('video_note')->nullable();
            $table->json('voice')->nullable();
            $table->text('caption')->nullable();
            $table->json('caption_entities')->nullable();
            $table->boolean('show_caption_above_media')->nullable();
            $table->boolean('has_media_spoiler')->nullable();
            $table->json('contact')->nullable();
            $table->json('dice')->nullable();
            $table->json('game')->nullable();
            $table->json('venue')->nullable();
            $table->json('location')->nullable();
            $table->json('new_chat_members')->nullable();
            $table->string('new_chat_title')->nullable();
            $table->json('new_chat_photo')->nullable();
            $table->boolean('delete_chat_photo')->nullable();
            $table->boolean('group_chat_created')->nullable();
            $table->boolean('supergroup_chat_created')->nullable();
            $table->boolean('channel_chat_created')->nullable();
            $table->json('message_auto_delete_timer_changed')->nullable();
            $table->bigInteger('migrate_to_chat_id')->nullable();
            $table->bigInteger('migrate_from_chat_id')->nullable();
            $table->json('invoice')->nullable();
            $table->json('successful_payment')->nullable();
            $table->json('refunded_payment')->nullable();
            $table->json('users_shared')->nullable();
            $table->json('chat_shared')->nullable();
            $table->string('connected_website')->nullable();
            $table->json('write_access_allowed')->nullable();
            $table->json('passport_data')->nullable();
            $table->json('proximity_alert_triggered')->nullable();
            $table->json('boost_added')->nullable();
            $table->json('chat_background_set')->nullable();
            $table->json('forum_topic_created')->nullable();
            $table->json('forum_topic_edited')->nullable();
            $table->json('forum_topic_closed')->nullable();
            $table->json('forum_topic_reopened')->nullable();
            $table->json('general_forum_topic_hidden')->nullable();
            $table->json('general_forum_topic_unhidden')->nullable();
            $table->json('giveaway_created')->nullable();
            $table->json('giveaway')->nullable();
            $table->json('giveaway_winners')->nullable();
            $table->json('giveaway_completed')->nullable();
            $table->json('video_chat_scheduled')->nullable();
            $table->json('video_chat_started')->nullable();
            $table->json('video_chat_ended')->nullable();
            $table->json('video_chat_participants_invited')->nullable();
            $table->json('web_app_data')->nullable();
            $table->json('reply_markup')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_messages(): void
    {
        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->after('reply_markup', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\User::class, 'telegram_user_id')->nullable()->constrained('telegram_users');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Chat::class, 'telegram_sender_chat_id')->nullable()->constrained('telegram_chats');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\User::class, 'sender_business_bot_id')->nullable()->constrained('telegram_users');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Chat::class, 'telegram_chat_id')->nullable()->constrained('telegram_chats');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Message::class, 'telegram_reply_to_message_id')->nullable()->constrained('telegram_messages');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\User::class, 'telegram_via_bot_id')->nullable()->constrained('telegram_users');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Attachments\Poll::class, 'telegram_poll_id')->nullable()->constrained('telegram_polls');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\User::class, 'telegram_left_chat_member_id')->nullable()->constrained('telegram_users');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Message::class, 'telegram_pinned_message_id')->nullable()->constrained('telegram_messages');
            });
        });
    }

    private function create_telegram_business_connections(): void
    {
        Schema::create('telegram_business_connections', function (Blueprint $table) {
            $table->id('telegram_business_connection_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->bigInteger('id')->unique();
            $table->bigInteger('user_chat_id')->nullable();
            $table->timestamp('date')->nullable();
            $table->boolean('can_reply')->nullable();
            $table->boolean('is_enabled')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_business_connections(): void
    {
        Schema::table('telegram_business_connections', function (Blueprint $table) {
            $table->after('is_enabled', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\User::class, 'telegram_user_id')->nullable()->constrained('telegram_users');
            });
        });
    }

    private function create_telegram_business_messages_deleted()
    {
        Schema::create('telegram_business_messages_deleted', function (Blueprint $table) {
            $table->id('telegram_business_messages_deleted_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->bigInteger('business_connection_id')->nullable();
            $table->json('message_ids')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_business_messages_deleted()
    {
        Schema::table('telegram_business_messages_deleted', function (Blueprint $table) {
            $table->after('message_ids', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Chat::class, 'telegram_chat_id')->nullable()->constrained('telegram_chats');
            });
        });
    }

    private function create_telegram_message_reaction_updated(): void
    {
        Schema::create('telegram_message_reaction_updated', function (Blueprint $table) {
            $table->id('telegram_message_reaction_updated_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->bigInteger('message_id')->nullable();
            $table->dateTime('date')->nullable();
            $table->json('old_reaction')->nullable();
            $table->json('new_reaction')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_message_reaction_updated(): void
    {
        Schema::table('telegram_message_reaction_updated', function (Blueprint $table) {
            $table->after('new_reaction', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Chat::class, 'telegram_chat_id')->nullable()->constrained('telegram_chats');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\User::class, 'telegram_user_id')->nullable()->constrained('telegram_users');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Chat::class, 'telegram_actor_chat_id')->nullable()->constrained('telegram_chats');
            });
        });
    }

    private function create_telegram_message_reaction_count_updated(): void
    {
        Schema::create('telegram_message_reaction_count_updated', function (Blueprint $table) {
            $table->id('telegram_message_reaction_count_updated_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->bigInteger('message_id')->nullable();
            $table->timestamp('date')->nullable();
            $table->json('reactions')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_message_reaction_count_updated(): void
    {
        Schema::table('telegram_message_reaction_count_updated', function (Blueprint $table) {
            $table->after('reactions', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Chat::class, 'telegram_chat_id')->nullable()->constrained('telegram_chats');
            });
        });
    }

    private function create_telegram_inline_queries(): void
    {
        Schema::create('telegram_inline_queries', function (Blueprint $table) {
            $table->id('telegram_inline_query_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->string('id')->nullable();
            $table->string('query')->nullable();
            $table->string('offset')->nullable();
            $table->string('chat_type')->nullable();
            $table->json('location')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_inline_queries(): void
    {
        Schema::table('telegram_inline_queries', function (Blueprint $table) {
            $table->after('location', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\User::class, 'telegram_user_id')->nullable()->constrained('telegram_users');
            });
        });
    }

    private function create_telegram_chosen_inline_results(): void
    {
        Schema::create('telegram_chosen_inline_results', function (Blueprint $table) {
            $table->id('telegram_chosen_inline_result_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->string('result_id')->nullable();
            $table->json('location')->nullable();
            $table->string('inline_message_id')->nullable();
            $table->string('query')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_chosen_inline_results(): void
    {
        Schema::table('telegram_chosen_inline_results', function (Blueprint $table) {
            $table->after('query', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\User::class, 'telegram_user_id')->nullable()->constrained('telegram_users');
            });
        });
    }

    private function create_telegram_callback_queries(): void
    {
        Schema::create('telegram_callback_queries', function (Blueprint $table) {
            $table->id('telegram_callback_query_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->string('id')->nullable();
            $table->string('inline_message_id')->nullable();
            $table->string('chat_instance')->nullable();
            $table->string('data')->nullable();
            $table->string('game_short_name')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_callback_queries(): void
    {
        Schema::table('telegram_callback_queries', function (Blueprint $table) {
            $table->after('game_short_name', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\User::class, 'telegram_user_id')->nullable()->constrained('telegram_users');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Message::class, 'telegram_message_id')->nullable()->constrained('telegram_messages');
            });
        });
    }

    private function create_telegram_shipping_queries(): void
    {
        Schema::create('telegram_shipping_queries', function (Blueprint $table) {
            $table->id('telegram_shipping_query_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->string('id')->nullable();
            $table->string('invoice_payload')->nullable();
            $table->json('shipping_address')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_shipping_queries(): void
    {
        Schema::table('telegram_shipping_queries', function (Blueprint $table) {
            $table->after('shipping_address', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\User::class, 'telegram_user_id')->nullable()->constrained('telegram_users');
            });
        });
    }

    private function create_telegram_pre_checkout_queries(): void
    {
        Schema::create('telegram_pre_checkout_queries', function (Blueprint $table) {
            $table->id('telegram_pre_checkout_query_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->string('id')->nullable();
            $table->string('currency')->nullable();
            $table->bigInteger('total_amount')->nullable();
            $table->string('invoice_payload')->nullable();
            $table->string('shipping_option_id')->nullable();
            $table->json('order_info')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_pre_checkout_queries(): void
    {
        Schema::table('telegram_pre_checkout_queries', function (Blueprint $table) {
            $table->after('order_info', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\User::class, 'telegram_user_id')->nullable()->constrained('telegram_users');
            });
        });
    }

    private function create_telegram_paid_media_purchased(): void
    {
        Schema::create('telegram_paid_media_purchased', function (Blueprint $table) {
            $table->id('telegram_paid_media_purchased_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->string('paid_media_payload')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_paid_media_purchased(): void
    {
        Schema::table('telegram_paid_media_purchased', function (Blueprint $table) {
            $table->after('paid_media_payload', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\User::class, 'telegram_user_id')->nullable()->constrained('telegram_users');
            });
        });
    }

    private function create_telegram_polls(): void
    {
        Schema::create('telegram_polls', function (Blueprint $table) {
            $table->id('telegram_poll_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->bigInteger('id')->nullable();
            $table->string('question')->nullable();
            $table->bigInteger('total_voter_count')->nullable();
            $table->boolean('is_closed')->nullable();
            $table->boolean('is_anonymous')->nullable();
            $table->string('type')->nullable();
            $table->boolean('allows_multiple_answers')->nullable();
            $table->bigInteger('correct_option_id')->nullable();
            $table->text('explanation')->nullable();
            $table->bigInteger('open_period')->nullable();
            $table->timestamp('close_date')->nullable();

            $table->timestamps();
        });
    }

    private function create_telegram_poll_answers(): void
    {
        Schema::create('telegram_poll_answers', function (Blueprint $table) {
            $table->id('telegram_poll_answer_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->bigInteger('poll_id')->nullable();
            $table->json('option_ids')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_poll_answers(): void
    {
        Schema::table('telegram_poll_answers', function (Blueprint $table) {
            $table->after('option_ids', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Chat::class, 'telegram_chat_id')->nullable()->constrained('telegram_chats');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\User::class, 'telegram_user_id')->nullable()->constrained('telegram_users');
            });
        });
    }

    private function create_telegram_chat_member_updated(): void
    {
        Schema::create('telegram_chat_member_updated', function (Blueprint $table) {
            $table->id('telegram_chat_member_updated_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->timestamp('date')->nullable();
            $table->json('old_chat_member')->nullable();
            $table->json('new_chat_member')->nullable();
            $table->json('invite_link')->nullable();
            $table->boolean('via_join_request')->nullable();
            $table->boolean('via_chat_folder_invite_link')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_chat_member_updated(): void
    {
        Schema::table('telegram_chat_member_updated', function (Blueprint $table) {
            $table->after('via_chat_folder_invite_link', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Chat::class, 'telegram_chat_id')->nullable()->constrained('telegram_chats');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\User::class, 'telegram_user_id')->nullable()->constrained('telegram_users');
            });
        });
    }

    private function create_telegram_chat_join_requests(): void
    {
        Schema::create('telegram_chat_join_requests', function (Blueprint $table) {
            $table->id('telegram_chat_join_request_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->bigInteger('user_chat_id')->nullable();
            $table->timestamp('date')->nullable();
            $table->string('bio')->nullable();
            $table->json('invite_link')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_chat_join_requests(): void
    {
        Schema::table('telegram_chat_join_requests', function (Blueprint $table) {
            $table->after('invite_link', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Chat::class, 'telegram_chat_id')->nullable()->constrained('telegram_chats');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\User::class, 'telegram_user_id')->nullable()->constrained('telegram_users');
            });
        });
    }

    private function create_telegram_chat_boost_updated(): void
    {
        Schema::create('telegram_chat_boost_updated', function (Blueprint $table) {
            $table->id('telegram_chat_boost_updated_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->json('boost')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_chat_boost_updated(): void
    {
        Schema::table('telegram_chat_boost_updated', function (Blueprint $table) {
            $table->after('boost', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Chat::class, 'telegram_chat_id')->nullable()->constrained('telegram_chats');
            });
        });
    }

    private function create_telegram_chat_boost_removed(): void
    {
        Schema::create('telegram_chat_boost_removed', function (Blueprint $table) {
            $table->id('telegram_chat_boost_removed_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->string('boost_id')->nullable();
            $table->timestamp('remove_date')->nullable();
            $table->json('source')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_chat_boost_removed(): void
    {
        Schema::table('telegram_chat_boost_removed', function (Blueprint $table) {
            $table->after('source', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Chat::class, 'telegram_chat_id')->nullable()->constrained('telegram_chats');
            });
        });
    }

    private function create_telegram_users()
    {
        Schema::create('telegram_users', function (Blueprint $table) {
            $table->id('telegram_user_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->bigInteger('id')->nullable();
            $table->boolean('is_bot')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('language_code')->nullable();
            $table->string('is_premium')->nullable();
            $table->string('added_to_attachment_menu')->nullable();
            $table->boolean('can_join_groups')->nullable();
            $table->boolean('can_read_all_group_messages')->nullable();
            $table->boolean('supports_inline_queries')->nullable();
            $table->boolean('can_connect_to_business')->nullable();
            $table->boolean('has_main_web_app')->nullable();

            $table->timestamps();
        });
    }

    private function create_telegram_chats()
    {
        Schema::create('telegram_chats', function (Blueprint $table) {
            $table->id('telegram_chat_id');

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\TelegramBot::class, 'telegram_bot_id')->nullable()->constrained('telegram_bots');

            $table->bigInteger('id')->nullable();
            $table->string('type')->nullable();
            $table->string('title')->nullable();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->boolean('is_forum')->nullable();
            $table->bigInteger('accent_color_id')->nullable();
            $table->bigInteger('max_reaction_count')->nullable();
            $table->json('photo')->nullable();
            $table->json('active_usernames')->nullable();
            $table->json('birthdate')->nullable();
            $table->json('business_intro')->nullable();
            $table->json('business_location')->nullable();
            $table->json('business_opening_hours')->nullable();
            $table->json('available_reactions')->nullable();
            $table->bigInteger('background_custom_emoji_id')->nullable();
            $table->bigInteger('profile_accent_color_id')->nullable();
            $table->bigInteger('profile_background_custom_emoji_id')->nullable();
            $table->bigInteger('emoji_status_custom_emoji_id')->nullable();
            $table->bigInteger('emoji_status_expiration_date')->nullable();
            $table->string('bio')->nullable();
            $table->boolean('has_private_forwards')->nullable();
            $table->boolean('has_restricted_voice_and_video_messages')->nullable();
            $table->boolean('join_to_send_messages')->nullable();
            $table->boolean('join_by_request')->nullable();
            $table->string('description')->nullable();
            $table->string('invite_link')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('can_send_paid_media')->nullable();
            $table->bigInteger('slow_mode_delay')->nullable();
            $table->bigInteger('unrestrict_boost_count')->nullable();
            $table->bigInteger('message_auto_delete_time')->nullable();
            $table->boolean('has_aggressive_anti_spam_enabled')->nullable();
            $table->boolean('has_hidden_members')->nullable();
            $table->boolean('has_protected_content')->nullable();
            $table->boolean('has_visible_history')->nullable();
            $table->string('sticker_set_name')->nullable();
            $table->boolean('can_set_sticker_set')->nullable();
            $table->string('custom_emoji_sticker_set_name')->nullable();
            $table->bigInteger('linked_chat_id')->nullable();
            $table->json('location')->nullable();

            $table->timestamps();
        });
    }

    private function add_foreign_keys_to_telegram_chats(): void
    {
        Schema::table('telegram_chats', function (Blueprint $table) {
            $table->after('location', function (Blueprint $table) {
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Chat::class, 'telegram_personal_chat_id')->nullable()->constrained('telegram_chats');
                $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Message::class, 'telegram_pinned_message_id')->nullable()->constrained('telegram_messages');
            });
        });
    }

    private function create_telegram_conversations(): void
    {
        Schema::create('telegram_conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(\Romanlazko\LaravelTelegram\Models\Types\Chat::class, 'telegram_chat_id')->unique()->constrained('telegram_chats');

            $table->json('notes')->nullable();
            $table->string('expectation')->nullable();

            $table->timestamps();
        });
    }
};
