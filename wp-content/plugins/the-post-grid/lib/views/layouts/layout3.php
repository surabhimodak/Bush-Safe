<?php
/**
 * @var integer $pID
 * @var string $grid
 * @var string $pLink
 * @var string $class
 * @var bool $link
 * @var string $link_target
 * @var string $title_tag
 * @var string $image_area
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
        $html .= '<div class="row">';
			if(!empty($imgSrc)) {
				$html .= "<div class='{$image_area}'>";
				$html .= '<div class="rt-img-holder">';
				if($overlay && $link) {
                    $html .= sprintf('<div class="overlay">
					                            <div class="link-holder">
                                                        <a class="view-details" href="%s"%s><i class="fa fa-info"></i></a>
                                                </div>
                                            </div>', $pLink, $link_target);
				}
                if($link) {
                    $html .= sprintf('<a href="%s"%s><img class="img-responsive rounded" src="%s" alt="%s"></a>', $pLink,$link_target, $imgSrc, $title);
                }else{
                    $html .= "<img class='img-responsive rounded' src='{$imgSrc}' alt='{$title}'>";
                }
				$html .= '</div>';
				$html .= '</div>';
			}else{
				$content_area = "rt-col-md-12";
			}
            $html .= "<div class='{$content_area}'>";
                $html .= '<div class="rt-detail">';
                        if(in_array('title', $items)){
                            if($link) {
                                $html .= sprintf('<%1$s class="entry-title"><a href="%2$s"%4$s>%3$s</a></%1$s>', $title_tag, $pLink, $title,$link_target);
                            }else{
                                $html .= sprintf('<%1$s class="entry-title">%2$s</%1$s>', $title_tag, $title);
                            }
                        }
                        $metaHtml = null;
                        if(in_array('post_date', $items) && $date){
                            $metaHtml .= "<span class='date-meta'><i class='fa fa-calendar'></i> {$date}</span>";
                        }
                        if(in_array('author', $items)){
                            $metaHtml .= "<span class='author'><i class='fa fa-user'></i>{$author}</span>";
                        }
                        if(in_array('categories', $items) && $categories){
                            $metaHtml .= "<span class='categories-links'><i class='fa fa-folder-open-o'></i>{$categories}</span>";
                        }
                        if(in_array('tags', $items) && $tags){
                            $metaHtml .= "<span class='post-tags-links'><i class='fa fa-tags'></i>{$tags}</span>";
                        }
                        if(in_array('comment_count', $items) && $comment){
                            $metaHtml .= "<span class='comment-link'><i class='fa fa-comments-o'></i>{$comment}</span>";
                        }
                        if(!empty($metaHtml)){
                            $html .="<div class='post-meta-user'>{$metaHtml}</div>";
                        }

                        if(in_array('excerpt', $items)){
                            $html .= "<div class='post-content'>{$excerpt}</div>";
                        }
                        if(in_array('read_more', $items) && $link){
                            $html .= sprintf('<div class="read-more"><a href="%s"%s>%s</a></div>', $pLink, $link_target, $read_more_text);
                        }
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';
    $html .= '</div>';
$html .='</div>';

echo $html;