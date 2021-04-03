export default function getQuerySelector(elem) {

	var element = elem;
	var str = '';

	function loop(element) {

		// stop here = element has ID
		if(element.getAttribute('id')) {
			str = str.replace(/^/, ' #' + element.getAttribute('id'));
			str = str.replace(/\s/, '');
			str = str.replace(/\s/g, ' > ');
			return str;
		}

		// stop here = element is body
		if(document.body === element) {
			str = str.replace(/^/, ' body');
			str = str.replace(/\s/, '');
			str = str.replace(/\s/g, ' > ');
			return str;
		}

		// concat all classes in 'queryselector' style
		if(element.getAttribute('class')) {
			var elemClasses = '.';
			elemClasses += element.getAttribute('class');
			elemClasses = elemClasses.replace(/\s/g, '.');
			elemClasses = elemClasses.replace(/^/g, ' ');
			var classNth = '';

			// check if element class is the unique child
			var childrens = element.parentNode.children;

			if(childrens.length < 2) {
				return;
			}

			var similarClasses = [];

			for(var i = 0; i < childrens.length; i++) {
				if(element.getAttribute('class') == childrens[i].getAttribute('class')) {
					similarClasses.push(childrens[i]);
				}
			}

			if(similarClasses.length > 1) {
				for(var j = 0; j < similarClasses.length; j++) {
					if(element === similarClasses[j]) {
						j++;
						classNth = ':nth-of-type(' + j + ')';
						break;
					}
				}
			}

			str = str.replace(/^/, elemClasses + classNth);

		}
		else{

			// get nodeType
			var name = element.nodeName;
			name = name.toLowerCase();
			var nodeNth = '';

			var childrens = element.parentNode.children;

			if(childrens.length > 2) {
				var similarNodes = [];

				for(var i = 0; i < childrens.length; i++) {
					if(element.nodeName == childrens[i].nodeName) {
						similarNodes.push(childrens[i]);
					}
				}

				if(similarNodes.length > 1) {
					for(var j = 0; j < similarNodes.length; j++) {
						if(element === similarNodes[j]) {
							j++;
							nodeNth = ':nth-of-type(' + j + ')';
							break;
						}
					}
				}

			}

			str = str.replace(/^/, ' ' + name + nodeNth);

		}

		if(element.parentNode) {
			loop(element.parentNode);
		}
		else {
			str = str.replace(/\s/g, ' > ');
			str = str.replace(/\s/, '');
			return str;
		}

	}

	if (typeof element.getAttribute !== 'undefined') {
		loop(element);
	}

	return str;


}
