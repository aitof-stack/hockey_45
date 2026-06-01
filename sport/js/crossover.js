let isMobile=0;
let isDesktop=0;

const uaDataIsMobile = window.navigator.userAgentData?.mobile;

isMobile = typeof uaDataIsMobile === 'boolean'
  ? uaDataIsMobile
  : legacyIsMobileCheck(window.navigator.userAgent);

function legacyIsMobileCheck(ua)
	{
	if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua)) {
				return 1;
			} else {
			return 0;
		}
	}
if (!isMobile) isDesktop=1;

let news_timeout=null;
window.addEventListener('load',function(){
	let slider=document.getElementById('news_slider');
	if (slider && isMobile)
		{
		let x_begin=0;
		let y_begin=0;
		let x_end=0;
		let y_end=0;
		let y_offset=0;
		let x_offset=0;
		let slider_width=slider.offsetWidth;
		let slider_enter_id=null;
		slider.ondragstart=function(){return false;};
		slider.ontouchmove = function() {
			return false;
			};
		
		function scrollToSmoothly(pos, time) {
			let currentPos = window.pageYOffset;
			let start = null;
			if(time == null) time = 300;
			pos = +pos, time = +time;
			window.requestAnimationFrame(function step(currentTime) {
					start = !start ? currentTime : start;
					let progress = currentTime - start;
					if (currentPos < pos) {
							window.scrollTo(0, ((pos - currentPos) * progress / time) + currentPos);
					} else {
							window.scrollTo(0, currentPos - ((currentPos - pos) * progress / time));
					}
					if (progress < time) {
							window.requestAnimationFrame(step);
					} else {
							window.scrollTo(0, pos);
					}
			});
		}
		slider.addEventListener('pointerdown',function(ev){
			if (ev.isPrimary && ev.pointerType=='touch')
				{
				slider_enter_id=ev.pointerId;
				//slider.setPointerCapture(ev.pointerId);
				x_begin=ev.clientX;
				y_begin=ev.clientY;
				x_end=0;
				y_end=0;
				y_offset=window.pageYOffset;
				x_offset=window.pageXOffset;
				slider_width=slider.offsetWidth;
				}
			});
		slider.addEventListener('pointermove',function(ev) {
			
			if (ev.isPrimary && ev.pointerType=='touch')
				{
				y_end=ev.clientY;
				let diffY=y_end-y_begin;
				let stopY=y_offset-diffY;
				//window.scrollTo(x_offset,stopY);
				scrollToSmoothly(stopY);
				}
		});
		slider.addEventListener('pointerup',function(ev) {
			if (ev.pointerId==slider_enter_id)
				{
				x_end=ev.clientX;
				y_end=ev.clientY;
				let diffX=x_end-x_begin;
				let diffY=y_end-y_begin;
				let adiff=diffX/diffY;
				if (Math.abs((diffX/slider_width)*100)>5 && Math.abs(adiff)>3)
					{
					clearInterval(news_timeout);
					if (diffX<0) news_view('right'); else news_view('left');
					}
			slider_enter_id=null;
			}
		});
		}
	let listers=document.querySelectorAll('.slider-lister');
	for (let node of listers)
		{
		let direction='left';
		if (node.classList.contains('right')) direction='right';
		node.addEventListener('click',function(){
			if (news_timeout) clearInterval(news_timeout);
			news_view(direction);
		});
		}
	if (listers.length) news_timeout=setInterval(function(){news_view('right')},7000);
	
	let stage_switchers=document.querySelectorAll('.gt-stage-switcher');
	for (let node of stage_switchers)
		{
		node.addEventListener('click',function(){
			let block=node.closest('.gt-item');
			let dataStage = node.getAttribute('data-stage');
			let target=block.querySelector('[data-id="table-'+dataStage+'"]');

			if (target)
			{
				let act_tables=block.querySelectorAll('.active');
				for (let tbl of act_tables)
				{
					tbl.classList.remove('active');
				}
				
				target.classList.add('active');
			}
			
			this.classList.add('active');
			});
		}
});

let watched_video=[];
window.addEventListener('load',async function(){
	let video_hits=document.querySelectorAll('.video-hit[data-video-id]');
	let iframes=false;
	for (let node of video_hits)
		{
		if (!iframes && node.tagName=='IFRAME') iframes=true;
		node.addEventListener('click',async function(){
			let video_id=parseInt(this.getAttribute('data-video-id')) || 0;
			if (video_id)
				{
				fetch('/sport/hit.php?type=video&id='+video_id);
				}
		},{once:true});
		}
	if (iframes && video_hits.length)
		{
		window.addEventListener('blur', async function() {
			let a_elem=document.activeElement;
			if (a_elem.tagName == 'IFRAME' && a_elem.classList.contains('video-hit')) {
				let video_id=parseInt(a_elem.getAttribute('data-video-id')) || 0;
				if (video_id)
					{
					if (watched_video.indexOf(video_id,0)==-1)
						{
						watched_video.push(video_id);
						fetch('/sport/hit.php?type=video&id='+video_id);
						}
					}
			}
		});
		}
	let photo_hits=document.querySelectorAll('.photo-hit[data-photo-id]');
	for (let node of photo_hits)
		{
		node.addEventListener('click',async function(){
			let photo_id=parseInt(this.getAttribute('data-photo-id')) || 0;
			if (photo_id)
				{
				fetch('/sport/hit.php?type=photo&id='+photo_id);
				}
		},{once:true});
		}
	let slide_hits=document.querySelectorAll('.slide-hit');
	for (let node of slide_hits)
		{
		node.addEventListener('click',async function(){
			let slide_id=parseInt(this.getAttribute('data-slide-id')) || 0;
			if (slide_id)
				{
				fetch('/sport/hit.php?type=slide&id='+slide_id);
				}
		},{once:true});
		}
});

async function open_match_center(the_date,club,team,tour,ref,league)
	{
	let errors=[];
	let mc=document.getElementById('match_center');
	mc.classList.remove('hist-back-fix');
	let old_date=mc.getAttribute('data-date');
	let group_id=0;
	if (old_date && old_date==the_date)
		{
		let gr_selector=document.querySelector('.mc-match-group');
		if (gr_selector && gr_selector.value) group_id=gr_selector.value;
		}
	let mc_load=document.getElementById('match-center-loading');
	mc_load.innerHTML='';
	document.getElementById('mc-opener').checked=true;
	mc.classList.add('in-action');
	let nowdate=new Date();
	let nowstamp=nowdate.getTime();
	club=parseInt(club);
	team=parseInt(team);
	tour=parseInt(tour);
	ref=parseInt(ref);
	league=parseInt(league);
	try
		{
		let response=await fetch('/sport/match_center.php?date='+the_date+'&club_id='+club+'&team_id='+team+'&tour_id='+tour+'&ref_id='+ref+'&league_id='+league+'&refresh='+nowstamp);
			if (response.status==200)
				{
				let text=await response.text();
				if (text)
					{
					mc_load.innerHTML=text;
					mc.classList.remove('in-action');
					$('.mc-match-group, .mc-match-team-select').select2({
						matcher: oldMatcher(matchStart)
					});
					mc.setAttribute('data-date',the_date);
					if (group_id)
						{
						$('.mc-match-group').val(group_id).trigger('change');
						}
					
					}
					else errors.push('Не удалось получить данные. Попробуйте ещё раз');
				$('.select2-hidden-accessible:not([multiple])').on('select2:open', function( event ) {
					let g=document.querySelector('input.select2-search__field');
				 if (g) g.focus();
				});
				}
				else errors.push('Ошибка ответа сервера');
		}
		catch(er)
		{
		errors.push('Не удалось получить данные. Попробуйте ещё раз');
		}
	if (errors.length)
		{
		mc.classList.remove('in-action');
		document.getElementById('mc-opener').checked=false;
		alert(errors.join('; '));
		}
	}

function mc_group_select(e)
	{
	let group=e.value;
	if (group)
		{
		let filter_other = document.querySelector('select.mc-match-team-select');
		if (filter_other)
		{
			filter_other.value = '';
			filter_other.dispatchEvent(new Event('change'));
		}	
			
		document.getElementById('group_'+group).removeAttribute('hidden');
		let divs=document.querySelectorAll('.matches-scroller > div:not(#group_'+group+')');
		for (let node of divs)
			{
			node.setAttribute('hidden',true);
			}
		}
		else
		{
		let divs=document.querySelectorAll('.matches-scroller > div[hidden]');
		for (let node of divs)
			{
			node.removeAttribute('hidden');
			}
		}
	}

function mc_team_select(e)
{
	let team=e.value;
	if (team)
	{
		let filter_other = document.querySelector('select.mc-match-group');
		if (filter_other)
		{
			filter_other.value = '';
			filter_other.dispatchEvent(new Event('change'));
		}
		
		let groups_w = document.querySelectorAll('.matches-scroller .group-wrapper');
		for (let group_w of groups_w)
		{
			if (!group_w.querySelector('.mc-match[data-team1="'+team+'"], .mc-match[data-team2="'+team+'"]'))
			{
				group_w.setAttribute('hidden',true);
			}
			else
			{
				group_w.removeAttribute('hidden');
			}
		}
		
		let divs=document.querySelectorAll('.matches-scroller .mc-match');
		for (let node of divs)
		{
			if (node.getAttribute('data-team1') != team && node.getAttribute('data-team2') != team)
			{
				node.setAttribute('hidden',true);
			}
			else
			{
				node.removeAttribute('hidden');
			}
		}
	}
	else
	{
		let divs=document.querySelectorAll('.matches-scroller div[hidden]');
		for (let node of divs)
		{
			node.removeAttribute('hidden');
		}
	}
}

function news_view(direction)
	{
	let target_elem;
	let slider=document.getElementById('news_slider');
	if (slider.children && slider.children.length>1)
		{
		let active=slider.querySelector('.active');
		if (!active) return false;
		if (direction=='left')
			{
			target_elem=active.previousElementSibling || slider.lastElementChild;
			}
		else
			{
			target_elem=active.nextElementSibling || slider.firstElementChild;
			}
		if (target_elem)
			{
			let prev_elem=target_elem.previousElementSibling || slider.lastElementChild;
			let next_elem=target_elem.nextElementSibling || slider.firstElementChild;
			active.classList.remove('active');
			target_elem.classList.add('active');
			let news_prev=document.querySelector('.news-item.previous');
			if (news_prev) news_prev.classList.remove('previous');
			let news_next=document.querySelector('.news-item.next');
			if (news_next) news_next.classList.remove('next');
			if (prev_elem) prev_elem.classList.add('previous');
			if (next_elem) next_elem.classList.add('next');
			}
		}
		else clearInterval(news_timeout);
	}
	
function matchStart (term, text) {
	tt=term.toString();
	var cre = /([^ ]+)/g;
	var mas = tt.match(cre);
	var a=false;
	var b=true;
	mas.forEach(function(item,i,mas){
		var re=new RegExp(item,'ig');
		a=re.test(text);
		if (a==false) b=false;
	});
	return b;
}


function oldMatcher (matcher) {
    function wrappedMatcher (params, data) {
      var match = $.extend(true, {}, data);

      if (params.term == null || $.trim(params.term) === '') {
        return match;
      }

      if (data.children) {
        for (var c = data.children.length - 1; c >= 0; c--) {
          var child = data.children[c];
          var doesMatch = matcher(params.term, child.text, child);
          if (!doesMatch) {
            match.children.splice(c, 1);
          }
        }

        if (match.children.length > 0) {
          return match;
        }
      }

      if (matcher(params.term, data.text, data)) {
        return match;
      }

      return null;
    }

    return wrappedMatcher;
  }

document.addEventListener('DOMContentLoaded',function(){
	$('.select2-hidden-accessible:not([multiple])').on('select2:open', function( event ) {
    let g=document.querySelector('input.select2-search__field');
	 if (g) g.focus();
	});
});