If you are using this skin for the Space Station 13 wiki
You'll probably need a palette template to standardise a bunch of colours and not have to mess around with different themes.
Below is this template, you can copy it and add it to your wiki.

How to use it?
Simple, here is example for templates:
style="background-color: {{ColorPalette|{{{Color}}}|Primary}}"

And when you use your template, just type:
|Color = Supply (or any existing in palette color)

You can find more here(RU wiki) - https://skyrat.ss220.club/index.php/Template:ColorPalette

<includeonly>{{#switch: {{{1}}}
| Civilian =
{{#switch: {{{2|Primary}}}
| Opaque = var(--civilian-opaque)
| Primary = var(--civilian-primary)
| Secondary = var(--civilian-secondary)
}}
| Medical =
{{#switch: {{{2|Primary}}}
| Opaque = var(--medical-opaque)
| Primary = var(--medical-primary)
| Secondary = var(--medical-secondary)
}}
| Supply =
{{#switch: {{{2|Primary}}}
| Opaque = var(--supply-opaque)
| Primary = var(--supply-primary)
| Secondary = var(--supply-secondary)
}}
| Science =
{{#switch: {{{2|Primary}}}
| Opaque = var(--science-opaque)
| Primary = var(--science-primary)
| Secondary = var(--science-secondary)
}}
| Engineering =
{{#switch: {{{2|Primary}}}
| Opaque = var(--engineer-opaque)
| Primary = var(--engineer-primary)
| Secondary = var(--engineer-secondary)
}}
| Security =
{{#switch: {{{2|Primary}}}
| Opaque = var(--security-opaque)
| Primary = var(--security-primary)
| Secondary = var(--security-secondary)
}}
| Antag =
{{#switch: {{{2|Primary}}}
| Opaque = var(--antag-opaque)
| Primary = var(--antag-primary)
| Secondary = var(--antag-secondary)
}}
| Legal =
{{#switch: {{{2|Primary}}}
| Opaque = var(--legal-opaque)
| Primary = var(--legal-primary)
| Secondary = var(--legal-secondary)
}}
| Command =
{{#switch: {{{2|Primary}}}
| Opaque = var(--command-opaque)
| Primary = var(--command-primary)
| Secondary = var(--command-secondary)
}}
| Synthetic =
{{#switch: {{{2|Primary}}}
| Opaque = var(--synthetic-opaque)
| Primary = var(--synthetic-primary)
| Secondary = var(--synthetic-secondary)
}}
| CentCom =
{{#switch: {{{2|Primary}}}
| Opaque = var(--centcom-opaque)
| Primary = var(--centcom-primary)
| Secondary = var(--centcom-secondary)
}}
| Special =
{{#switch: {{{2|Primary}}}
| Opaque = var(--special-opaque)
| Primary = var(--special-primary)
| Secondary = var(--special-secondary)
}}
| Cyan =
{{#switch: {{{2|Primary}}}
| Opaque = var(--cyan-opaque)
| Primary = var(--cyan-primary)
| Secondary = var(--cyan-secondary)
}}
| Blue =
{{#switch: {{{2|Primary}}}
| Opaque = var(--blue-opaque)
| Primary = var(--blue-primary)
| Secondary = var(--blue-secondary)
}}
| Green =
{{#switch: {{{2|Primary}}}
| Opaque = var(--green-opaque)
| Primary = var(--green-primary)
| Secondary = var(--green-secondary)
}}
| Yellow =
{{#switch: {{{2|Primary}}}
| Opaque = var(--yellow-opaque)
| Primary = var(--yellow-primary)
| Secondary = var(--yellow-secondary)
}}
| Red =
{{#switch: {{{2|Primary}}}
| Opaque = var(--red-opaque)
| Primary = var(--red-primary)
| Secondary = var(--red-secondary)
}}
| Pink =
{{#switch: {{{2|Primary}}}
| Opaque = var(--pink-opaque)
| Primary = var(--pink-primary)
| Secondary = var(--pink-secondary)
}}
| Brown =
{{#switch: {{{2|Primary}}}
| Opaque = var(--brown-opaque)
| Primary = var(--brown-primary)
| Secondary = var(--brown-secondary)
}}
| Black =
{{#switch: {{{2|Primary}}}
| Opaque = var(--black-opaque)
| Primary = var(--black-primary)
| Secondary = var(--black-secondary)
}}
|
{{#switch: {{{2|Primary}}}
| Opaque = var(--gray-opaque)
| Primary = var(--gray-primary)
| Secondary = var(--gray-secondary)
}}
}}</includeonly><noinclude>
