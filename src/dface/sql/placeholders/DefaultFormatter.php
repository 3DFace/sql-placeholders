<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class DefaultFormatter implements Formatter, NodeVisitor {

	/** @var Callable */
	private $escape_func;
	private $anonymousIndex;

	/**
	 * @param $format Node
	 * @param $args
	 * @param $escape_func Callable
	 * @return PlainNode
	 */
	function format(Node $format, $args, $escape_func){
		$this->escape_func = $escape_func;
		$this->anonymousIndex = 1;
		return new PlainNode(0, $format->acceptVisitor($this, $args));
	}

	private function formatValue($val, $escape_func){
		if(is_array($val)){
			if(!empty($val)){
				$val = array_map($escape_func, $val);
				$val = implode(", ", $val);
			}else{
				$val = 'null';
			}
		}else{
			$val = $escape_func($val);
		}
		return $val;
	}

	function visitPlain(PlainNode $plain, $args){
		return $plain->text;
	}

	function visitString(StringNode $string, $args){
		return $string->quote.call_user_func($this->escape_func, $string->text).$string->quote;
	}

	function visitIdentityPlaceHolder(IdentityPlaceHolderNode $placeHolder, $args){
		$val = $placeHolder->source->acceptVisitor($this, $args);
		return $this->formatValue($val, function ($v){
			return "`".str_replace("`", "``", $v)."`";
		});
	}

	function visitStringPlaceHolder(StringPlaceHolderNode $placeHolder, $args){
		$val = $placeHolder->source->acceptVisitor($this, $args);
		$escape_func = $this->escape_func;
		return $this->formatValue($val, function ($v) use ($escape_func, $placeHolder){
			if(is_null($v) && !$placeHolder->notNull){
				$v = 'null';
			}else{
				if($placeHolder->forceNull && $v === ''){
					$v = 'null';
				}else{
					$v = "'".$escape_func(strval($v))."'";
				}
			}
			return $v;
		});
	}

	function visitBinaryPlaceHolder(BinaryPlaceHolderNode $placeHolder, $args){
		$val = $placeHolder->source->acceptVisitor($this, $args);
		return $this->formatValue($val, function ($v) use ($placeHolder){
			if(is_null($v)){
				return 'null';
			}else{
				if($v === ''){
					return "''";
				}else{
					return '0x'.bin2hex($v);
				}
			}
		});
	}

	function visitNumberPlaceHolder(NumberPlaceHolderNode $placeHolder, $args){
		$val = $placeHolder->source->acceptVisitor($this, $args);
		return $this->formatValue($val, function ($v) use ($placeHolder){
			if(is_null($v) && !$placeHolder->notNull){
				$v = 'null';
			}else{
				if($placeHolder->forceNull && $v == 0){
					$v = 'null';
				}else{
					$v = str_replace(',', '.', strval(0 + $v));
				}
			}
			return $v;
		});
	}

	function visitIntegerPlaceHolder(IntegerPlaceHolderNode $placeHolder, $args){
		$val = $placeHolder->source->acceptVisitor($this, $args);
		return $this->formatValue($val, function ($v) use ($placeHolder){
			if(is_null($v) && !$placeHolder->notNull){
				$v = 'null';
			}else{
				if($placeHolder->forceNull && $v == 0){
					$v = 'null';
				}else{
					$s = strval($v);
					try{
						$g = @gmp_init($s);
						$v = $g ? gmp_strval($g) : (int)$s;
					}catch(\Exception $e){
						$v = (int)$s;
					}
				}
			}
			return $v;
		});
	}

	function visitComposite(CompositeNode $composite, $args){
		$result = '';
		foreach($composite->nodes as $node){
			$result .= $node->acceptVisitor($this, $args);
		}
		return $result;
	}

	function visitKeyAnchor(KeyAnchorNode $keyAnchor, $args){
		if(is_array($args)){
			if(array_key_exists($keyAnchor->key, $args)){
				return $args[$keyAnchor->key];
			}else{
				throw new FormatterException('There is no argument at key "'.$keyAnchor->key.'"');
			}
		}elseif(is_object($args)){
			if(property_exists($args, $keyAnchor->key)){
				return $args->{$keyAnchor->key};
			}else{
				throw new FormatterException('There is no argument at key "'.$keyAnchor->key.'"');
			}
		}else{
			throw new FormatterException('Argument not supports access by key');
		}

	}

	function visitIndexAnchor(IndexAnchorNode $indexAnchor, $args){
		$args = $this->castToIndexArray($args);
		$i = $indexAnchor->index - 1;
		if(array_key_exists($i, $args)){
			return $args[$i];
		}else{
			throw new FormatterException('There is no argument at index '.$indexAnchor->index);
		}
	}

	function visitAnonymousAnchor(AnonymousAnchorNode $indexAnchor, $args){
		$args = $this->castToIndexArray($args);
		$i = $this->anonymousIndex++ - 1;
		if(array_key_exists($i, $args)){
			return $args[$i];
		}else{
			throw new FormatterException('There is no argument at index '.($i + 1));
		}
	}

	protected function castToIndexArray($args){
		if(is_array($args)){
			$args = array_values($args);
		}elseif(is_object($args)){
			$args = get_object_vars($args);
			$args = array_values($args);
		}else{
			throw new FormatterException("Argument of type '".gettype($args)."' not supports access by index");
		}
		return $args;
	}

}
