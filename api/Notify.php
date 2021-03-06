<?php
if (defined('PHP7')) {
    eval("declare(strict_types=1);");
}

/**
 * Класс уведомлений
 */
class Notify extends Simpla
{
    /**
     * @param $to
     * @param $subject
     * @param $message
     * @param string $from
     * @param string $reply_to
     */
    function email($to, $subject, $message, $from = '', $reply_to = '')
    {
        $headers = "MIME-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=utf-8; \r\n";
        $headers .= "From: $from\r\n";
        if (!empty($reply_to))
            $headers .= "reply-to: $reply_to\r\n";

        $subject = "=?utf-8?B?" . base64_encode($subject) . "?=";

        @mail($to, $subject, $message, $headers);
    }

    /**
     * @param $order_id
     * @return bool
     */
    public function email_order_user($order_id)
    {
        if (!($order = $this->orders->get_order(intval($order_id))) || empty($order['email'])) {
            return false;
        }

        if (!$purchases = $this->orders->get_purchases(array('order_id' => $order['id']))) {
            return false;
        }

        $products_ids = array();
        $variants_ids = array();

        foreach ($purchases as $purchase) {
            $products_ids[] = $purchase['product_id'];
            $variants_ids[] = $purchase['variant_id'];
        }

        if ($products = $this->products->get_products(array('id' => $products_ids))) {
            $variants = $this->variants->get_variants(array('grouped' => 'product_id', 'id' => $variants_ids));
        }

        foreach ($purchases as $k => $pr) {
            if (!empty($products[$pr['product_id']])) {
                $purchases[$k]['product'] = $products[$pr['product_id']];
            }
            if (!empty($variants[$pr['product_id']])) {
                $purchases[$k]['variants'] = $variants[$pr['product_id']];
            }
        }
        $this->design->assign('purchases', $purchases);

        // Способ доставки
        $delivery = $this->delivery->get_delivery($order['delivery_id']);
        $this->design->assign('delivery', $delivery);

        $this->design->assign('order', $order);
        $this->design->assign('purchases', $purchases);

        // Отправляем письмо
        $email_template = $this->design->fetch($this->config->root_dir . 'design/' . $this->settings->theme . '/html/email_order.tpl');
        $subject = $this->design->get_var('subject');

        $this->email($order['email'], $subject, $email_template, $this->settings->notify_from_email);

    }


    /**
     * @param $order_id
     * @return bool
     */
    public function email_order_admin($order_id)
    {
        if (!($order = $this->orders->get_order(intval($order_id)))) {
            return false;
        }

        if (!$purchases = $this->orders->get_purchases(array('order_id' => $order['id']))) {
            return false;
        }


        $products_ids = array();
        $variants_ids = array();

        foreach ($purchases as $purchase) {
            $products_ids[] = $purchase['product_id'];
            $variants_ids[] = $purchase['variant_id'];
        }

        if ($products = $this->products->get_products(array('id' => $products_ids))) {
            $variants = $this->variants->get_variants(array('grouped' => 'product_id', 'id' => $variants_ids));
        }

        foreach ($purchases as $k => $pr) {
            if (!empty($products[$pr['product_id']])) {
                $purchases[$k]['product'] = $products[$pr['product_id']];
            }
            if (!empty($variants[$pr['product_id']])) {
                $purchases[$k]['variants'] = $variants[$pr['product_id']];
            }
        }
        $this->design->assign('purchases', $purchases);

        // Способ доставки
        $delivery = $this->delivery->get_delivery($order['delivery_id']);
        $this->design->assign('delivery', $delivery);

        // Пользователь
        $user = $this->users->get_user(intval($order['user_id']));
        $this->design->assign('user', $user);

        $this->design->assign('order', $order);


        // В основной валюте
        $this->design->assign('main_currency', $this->money->get_currency());

        // Отправляем письмо
        $email_template = $this->design->fetch($this->config->root_dir . 'simpla/design/html/email_order_admin.tpl');
        $subject = $this->design->get_var('subject');
        $this->email($this->settings->order_email, $subject, $email_template, $this->settings->notify_from_email);

    }


    /**
     * @param $comment_id
     * @return bool
     */
    public function email_comment_admin($comment_id)
    {
        if (!($comment = $this->comments->get_comment(intval($comment_id))))
            return false;

        if ($comment['type'] == 'product')
            $comment['product'] = $this->products->get_product(intval($comment['object_id']));
        if ($comment['type'] == 'blog')
            $comment['post'] = $this->blog->get_post(intval($comment['object_id']));

        $this->design->assign('comment', $comment);

        // Отправляем письмо
        $email_template = $this->design->fetch($this->config->root_dir . 'simpla/design/html/email_comment_admin.tpl');
        $subject = $this->design->get_var('subject');
        $this->email($this->settings->comment_email, $subject, $email_template, $this->settings->notify_from_email);
    }

    /**
     * @param $user_id
     * @param $code
     * @return bool
     */
    public function email_password_remind($user_id, $code)
    {
        if (!($user = $this->users->get_user(intval($user_id))))
            return false;

        $this->design->assign('user', $user);
        $this->design->assign('code', $code);

        // Отправляем письмо
        $email_template = $this->design->fetch($this->config->root_dir . 'design/' . $this->settings->theme . '/html/email_password_remind.tpl');
        $subject = $this->design->get_var('subject');
        $this->email($user['email'], $subject, $email_template, $this->settings->notify_from_email);

        $this->design->smarty->clearAssign('user');
        $this->design->smarty->clearAssign('code');
    }

    /**
     * @param $feedback_id
     * @return bool
     */
    public function email_feedback_admin($feedback_id)
    {
        if (!($feedback = $this->feedback->get_feedback(intval($feedback_id))))
            return false;

        $this->design->assign('feedback', $feedback);

        // Отправляем письмо
        $email_template = $this->design->fetch($this->config->root_dir . 'simpla/design/html/email_feedback_admin.tpl');
        $subject = $this->design->get_var('subject');
        $this->email($this->settings->comment_email, $subject, $email_template, "$feedback->name < $feedback->email>", "$feedback->name < $feedback->email>");
    }


}
