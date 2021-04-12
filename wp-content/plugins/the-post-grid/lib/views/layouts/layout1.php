<?php
/**
 * @var integer $pID
 * @var string $grid
 * @var string $pLink
 * @var string $class
 * @var bool $link
 * @var string $link_target
 * @var string $title_tag
 * @var string $categories
 * @var string $read_more_text
 * @var string $date
 * @var string $author
 * @var bool $overlay
 * @var array $items
 */
$html = null;
$html .= sprintf('<div class="%s" data-id="%d">', esc_attr(implode(" ", [$grid, $class])), $pID);
    $html .= '<div class="rt-holder">';
		if(!empty($imgSrc)) {
			$html .= '<div class="rt-img-holder">';
			if($overlay && $link) {
				$html .= sprintf('<div class="overlay"><a class="view-details" href="%s"%s><i class="fa fa-info"></i></a></div>', $pLink, $link_target);
			}
			if($link) {
                $html .= sprintf('<a href="%s"%s><img class="img-responsive" src="%s" alt="%s"></a>', $pLink,$link_target, $imgSrc, $title);
            }else{
			    $html .= "<img class='img-responsive' src='{$imgSrc}' alt='{$title}'>";
            }
			$html .= '</div> ';
		}
        $html .= '<div class="rt-detail">';
            if(in_array('title', $items)){
                if($link) {
                    $html .= sprintf('<%1$s class="entry-title"><a href="%2$s"%4$s>%3$s</a></%1$s>', $title_tag, $pLink, $title,$link_target);
                }else{
                    $html .= sprintf('<%1$s class="entry-title">%2$s</%1$s>', $title_tag, $title);
                }
            }
            $postMetaTop = $postMetaMid =null;

            if(in_array('author', $items)){
                $postMetaTop .= "<span class='author'><i class='fa fa-user'></i>{$author}</span>";
            }
            if(in_array('post_date', $items) && $date){
                $postMetaTop .= "<span class='date'><i class='fa fa-calendar'></i>{$date}</span>";
            }
            if(in_array('comment_count', $items) && $comment){
                $postMetaTop .= "<span class='comment-link'><i class='fa fa-comments-o'></i>{$comment}</span>";
            }

            if(in_array('categories', $items) && $categories){
                $postMetaTop .= "<span class='categories-links'><i class='fa fa-folder-open-o'></i>{$categories}</span>";
            }
            if(in_array('tags', $items) && $tags){
                $postMetaMid .= "<span class='post-tags-links'><i class='fa fa-tags'></i>{$tags}</span>";
            }

            if(!empty($postMetaTop)){
                $html .= "<div class='post-meta-user'>{$postMetaTop}</div>";
            }
            if(!empty($postMetaMid)){
                $html .= "<div class='post-meta-tags'>{$postMetaMid}</div>";
            }
            if(in_array('excerpt', $items)){
                $html .= "<div class='post-content'>{$excerpt}</div>";
            }
            $postMetaBottom = null;
            if(in_array('read_more', $items) && $link){
                $postMetaBottom .= sprintf('<div class="read-more"><a href="%s"%s>%s</a></div>', $pLink, $link_target, $read_more_text);
            }
            if(!empty($postMetaBottom)){
                $html .= "<div class='post-meta'>$postMetaBottom</div>";
            }
        $html .= '</div>';
    $html .= '</div>';
$html .='</div>';

echo $html;