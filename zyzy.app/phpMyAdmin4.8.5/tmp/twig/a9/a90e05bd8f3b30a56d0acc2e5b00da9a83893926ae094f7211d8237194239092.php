<?php

/* console/query_action.twig */
class __TwigTemplate_a40c3781b626a7f99918e4644962ffcf75352e8bfaf214daf36f419b27631671 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<div class=\"";
        echo twig_escape_filter($this->env, ($context["parent_div_classes"] ?? null), "html", null, true);
        echo "\">
    ";
        // line 2
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["content_array"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["content"]) {
            // line 3
            echo "        ";
            if ((isset($context["content"]) || array_key_exists("content", $context))) {
                // line 4
                echo "        <span class=\"";
                echo twig_escape_filter($this->env, $this->getAttribute($context["content"], 0, [], "array"), "html", null, true);
                echo "\">
            ";
                // line 5
                echo twig_escape_filter($this->env, $this->getAttribute($context["content"], 1, [], "array"), "html", null, true);
                echo "
            ";
                // line 6
                if ($this->getAttribute($context["content"], "extraSpan", [], "array", true, true)) {
                    // line 7
                    echo "                : <span>";
                    echo twig_escape_filter($this->env, $this->getAttribute($context["content"], "extraSpan", [], "array"), "html", null, true);
                    echo "</span>
            ";
                }
                // line 9
                echo "        </span>
        ";
            }
            // line 11
            echo "    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['content'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 12
        echo "</div>
";
    }

    public function getTemplateName()
    {
        return "console/query_action.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  58 => 12,  52 => 11,  48 => 9,  42 => 7,  40 => 6,  36 => 5,  31 => 4,  28 => 3,  24 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "console/query_action.twig", "D:\\Project\\phpstudy_pro\\x64\\Release\\WWW\\phpMyAdmin4.8.5\\templates\\console\\query_action.twig");
    }
}
