<?php

class pagination {

  public function __construct($data = array(), $query, $admin = 'no', $sts = '') {
    $this->total = $data[0];
    $this->start = 0;
    $this->text  = $data[1];
    $this->query = $query;
    $this->split = 10;
    $this->page  = $data[2];
    $this->admin = $admin;
    $this->modr  = (isset($sts->en_modr) ? $sts->en_modr : 'no');
    $this->flag  = (isset($data[3]) ? explode(',', $data[3]) : array());
  }

  public function perpage() {
    return PER_PAGE;
  }

  public function qstring() {
    $qstring = array();
    if (!empty($_GET)) {
      foreach ($_GET AS $k => $v) {
        if (is_array($v)) {
          foreach ($v AS $v2) {
            $qstring[] = $k . '[]=' . urlencode($v2);
          }
        } else {
          $merge = array_merge($this->flag, array(
            'p',
            'next',
            'deleted'
          ));
          if (!in_array($k, $merge)) {
            $qstring[] = $k . '=' . urlencode($v);
          }
        }
      }
    }
    return (!empty($qstring) ? '&amp;' . implode('&amp;', $qstring) : '');
  }

  public function setUrl($page) {
    switch($this->admin) {
      case 'yes':
        return $this->query . $page . pagination::qstring();
        break;
      default:
        switch($this->modr) {
          case 'yes':
            return str_replace('{page}', $page, $this->query);
            break;
          default:
            return $this->query . $page . pagination::qstring();
            break;
        }
        break;
    }
  }

  public function display() {
    $html = '';
    // How many pages?
    $this->num_pages = ceil($this->total / pagination::perpage());
    // If pages less than or equal to 1, display nothing..
    if ($this->num_pages <= 1) {
      return $html;
    }
    // Build pages..
    $current_page = $this->page;
    $begin        = $current_page - $this->split;
    $end          = $current_page + $this->split;
    if ($begin < 1) {
      $begin = 1;
      $end   = $this->split * 2;
    }
    if ($end > $this->num_pages) {
      $end   = $this->num_pages;
      $begin = $end - ($this->split * 2);
      $begin++;
      if ($begin < 1) {
        $begin = 1;
      }
    }
    if ($current_page != 1) {
      $html .= '<li class="hidden-xs hidden-sm"><a title="' . mc_safeHTML($this->text[0]) . '" href="' . pagination::setUrl(1) . '">' . $this->text[0] . '</a></li>' . mc_defineNewline();
      $html .= '<li class="hidden-xs hidden-sm"><a title="' . mc_safeHTML($this->text[1]) . '" href="' . pagination::setUrl(($current_page - 1)) . '">' . $this->text[1] . '</a></li>' . mc_defineNewline();
    } else {
      $html .= '<li class="disabled hidden-xs hidden-sm"><a href="#">' . $this->text[0] . '</a></li>' . mc_defineNewline();
      $html .= '<li class="disabled hidden-xs hidden-sm"><a href="#">' . $this->text[1] . '</a></li>' . mc_defineNewline();
    }
    for ($i = $begin; $i <= $end; $i++) {
      if ($i != $current_page) {
        $html .= '<li><a title="' . $i . '" href="' . pagination::setUrl($i) . '">' . $i . '</a></li>' . mc_defineNewline();
      } else {
        $html .= '<li class="active"><a href="#">' . $i . '</a></li>' . mc_defineNewline();
      }
    }
    if ($current_page != $this->num_pages) {
      $html .= '<li class="hidden-xs hidden-sm"><a title="' . mc_safeHTML($this->text[2]) . '" href="' . pagination::setUrl(($current_page + 1)) . '">' . $this->text[2] . '</a></li>' . mc_defineNewline();
      $html .= '<li class="hidden-xs hidden-sm"><a title="' . mc_safeHTML($this->text[3]) . '" href="' . pagination::setUrl($this->num_pages) . '">' . $this->text[3] . '</a></li>' . mc_defineNewline();
    } else {
      $html .= '<li class="disabled hidden-xs hidden-sm"><a href="#">' . $this->text[2] . '</a></li>' . mc_defineNewline();
      $html .= '<li class="disabled hidden-xs hidden-sm"><a href="#">' . $this->text[3] . '</a></li>' . mc_defineNewline();
    }
    return '<div class="mswpages"><ul class="pagination pagination-sm">' . mc_defineNewline() . trim($html) . mc_defineNewline() . '</ul></div>';
  }

}

?>