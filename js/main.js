window.addEventListener('load',function(){
	let news_list_adders=document.querySelectorAll('.news-list-adder');
	for (let node of news_list_adders)
		{
		node.addEventListener('click',function(){
			let block=node.closest('.news-list-block');
			let id=parseInt(block.getAttribute('data-id')) || 0;
			let page=parseInt(block.getAttribute('data-page')) || 0;
			page++;
			if (page && id) news_loader(node,block,id,page);
		});
		}
	window.addEventListener('scroll',scroll_header);
});

function scroll_header()
	{
	}

async function news_loader(node,block,id,page)
	{
	let errors=[];
	let parent_block=node.parentElement;
	try
		{
		let response=await fetch('/sport/article_loader.php?category='+id+'&page='+page);
			if (response.status==200)
				{
				let json=await response.json();
				if (json.items.length)
					{
					for (let i in json.items)
						{
						let item=json.items[i];
						let wrapper=document.createElement('div');
						wrapper.classList.add('news-list-item');
						if (item.date)
							{
							let date_elem=document.createElement('p');
							date_elem.classList.add('news-list-item-date');
							date_elem.innerText=item.date;
							wrapper.append(date_elem);
							}
						let link_elem=document.createElement('a');
						link_elem.classList.add('news-list-item-header');
						if (item.external_link)
							{
							link_elem.href=item.external_link;
							link_elem.setAttribute('target','_blank');
							link_elem.setAttribute('rel','noopener');
							}
							else
							{
							link_elem.href='/sport/article/'+(item.alias ? item.alias : item.id);
							}
						link_elem.title=item.anons;
						link_elem.innerText=item.head;
						wrapper.append(link_elem);
						parent_block.before(wrapper);
						}
					}
				if (json.has_next_page)
					{
					block.setAttribute('data-page',page);
					}
					else
					{
					block.removeAttribute('data-page');
					node.remove();
					}
				}
				else errors.push('Ошибка ответа сервера');
		}
		catch(er)
		{
		errors.push('Не удалось получить данные. Попробуйте ещё раз');
		}
	if (errors.length)
		{
		alert(errors.join('; '));
		}
	}