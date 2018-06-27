[screenshot]: https://raw.githubusercontent.com/digicademy/incipitSearch/master/images/IncipitSearch_search_engine_for_annotated_music.png


# IncipitSearch - Search engine for music incipits

IncipitSearch is a web platform and search engine for music incipits.

Incipits are an initial sequence of notes that represent the melodic characteristics of a composition.

Incipits are a pragmatic approach for the categorization of notated music, in which the first few bars of a score are transcribed. Furthermore, statements on melodic characteristics of the music can be made, based on these short fragments. First and foremost, the IncipitSearch can be seen as a counterpart to conventional thematic-bibliographic catalogues and catalogues of works, as they have been presented in printed form for a long time.

You can search the catalogues using the virtual keyboard. [Plaine & Easie Code](http://www.iaml.info/plaine-easie-code) is used as standard syntax for the encoding of the music incipits. The Plaine & Easie Code can be also entered directly in the search field.


![Screenshot of music incipit search engine and platform IncipitSearch][screenshot]

IncipitSearch website: [https://incipitsearch.adwmainz.net](https://incipitsearch.adwmainz.net/)

For an overview of functionalities and background see the presentation ["IncipitSearch - A common interface for searching in music repositories"](https://annaneo.github.io/DH-2018-Budapest-IncipitSearch) held at the Digital Humanities 2018 conference in Budapest.

# API and LOD

All content that can be searched in IncipitSearch can be also accessed using the API, which is basically a elasticsearch endpoint. The API is available at [https://incipitsearch.adwmainz.net/json/](https://incipitsearch.adwmainz.net/json/). For further information see the [documentation](https://incipitsearch.adwmainz.net/api/).

# Participation

You can integrate your repository in IncipitSearch and you can also use IncipitSearch as a search engine in your own repository. Learn [here](https://incipitsearch.adwmainz.net/en/participation/) how to participate and how to reintegrate IncipitSearch.

# License and Contribution

IncipitSearch is licensed under MIT License.

IncipitSearch is developed at the [Digital Academy](https://www.digitale-akademie.de) of the [Academy of Sciences and Literatur | Mainz](https://www.adwmainz.de). 

# Contributors

[Anna Neovesky](http://www.adwmainz.de/mitarbeiter/profil/anna-neovesky.html) (Idea, Concept, Application Development, Data Processing) 

Gabriel Reimers (Idea, Concept, Application Development)

[Frederic von Vlahovits](http://www.adwmainz.de/mitarbeiter/profil/frederic-von-vlahovits.html) (Frontend Development, Design, Documentation)

[Torsten Schrade](http://www.adwmainz.de/mitarbeiter/profil/prof-torsten-schrade.html) (Metadata schema, LOD, DevOps)


# Used Software and Standards

The [Verovio JavaScript Toolkit](http://www.verovio.org/javascript.xhtml) is used for rendering the incipits. Verovio is developed by [Swiss RISM Office](http://rism-ch.org/) with the support of the Swiss National Science Foundation. The source code is available on [GitHub](https://github.com/rism-ch/verovio)  under the LGPLv3 license. 

[Plaine & Easie Code](http://www.iaml.info/plaine-easie-code) is used for the incipit encoding and representation.


