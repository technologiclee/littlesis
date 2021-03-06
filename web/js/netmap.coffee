# requires D3!

class LittlesisApi

  constructor: (key) ->
    @key = key
    @base_url = "http://api.littlesis.org/"

  entities_and_rels_url: (entity_ids) ->
    @base_url + "map/entities.json?entity_ids=" + entity_ids.join(",") + "&_key=" + @key

  entities_and_rels: (entity_ids, callback) ->
    $.ajax({
      url: @entities_and_rels_url(entity_ids),
      success: callback,
      error: -> alert("There was an error retrieving data from the API")
      dataType: "json"
    });  

  get_add_entity_data: (entity_id, entity_ids, callback) ->
    $.ajax({
      url: @base_url + "map/addEntityData.json",
      data: { "entity_id": entity_id, "entity_ids": entity_ids },
      success: callback,
      error: -> alert("There was an error retrieving data from the API"),
      type: "GET",
      dataType: "json"
    })    

  get_add_related_entities_data: (entity_id, num, entity_ids, rel_ids, include_cats = [], callback) ->
    $.ajax({
      url: @base_url + "map/addRelatedEntitiesData.json",
      data: { "entity_id": entity_id, "num": num, "entity_ids": entity_ids, "rel_ids": rel_ids, "include_cat_ids": include_cats },
      success: callback,
      error: -> alert("There was an error retrieving data from the API"),
      type: "GET",
      dataType: "json"
    })   

  search_entities: (q, callback) ->
    $.ajax({
      url: @base_url + "map/searchEntities.json",
      data: { "q": q },
      success: callback,
      error: -> alert("There was an error retrieving data from the API"),
      type: "GET",
      dataType: "json"
    })

  create_map: (width, height, user_id, out_data, callback) ->
    $.ajax({
      url: @base_url + "map.json",
      data: { "width": width, "height": height, "user_id": user_id, "data" : JSON.stringify(out_data) },
      success: callback,
      error: -> alert("There was an error sending data to the API"),
      type: "POST",
      dataType: "json"
    })
    
  get_map: (id, callback) ->
    $.ajax({
      url: @base_url + "map/#{id}.json",
      success: callback,
      error: -> alert("There was an error retrieving data from the API"),
      dataType: "json"
    })

  update_map: (id, width, height, out_data, callback) ->
    $.ajax({
      url: @base_url + "map/#{id}/update.json",
      data: { "width": width, "height": height, "data" : JSON.stringify(out_data) },
      success: callback,
      error: -> alert("There was an error sending data to the API"),
      type: "POST",
      dataType: "json"
    })

    
class Netmap

  constructor: (width, height, parent_selector, key) ->
    @width = width
    @height = height
    @parent_selector = parent_selector
    @init_svg()
    @force_enabled = false
    @entity_background_opacity = 0.6
    @entity_background_color = "#fff"
    @entity_background_corner_radius = 5
    @distance = 225
    @api = new LittlesisApi(key)
    @init_callbacks()

  init_svg: ->
    @svg = d3.select(@parent_selector).append("svg")
      .attr("id", "svg")
      .attr("width", @width)
      .attr("height", @height)
    zoom = @svg.append('g')
      .attr("id", "zoom")
      .attr("fill", "#ffe")

    @zoom = d3.behavior.zoom()
    @zoom.scaleExtent([0.5, 5])
    zoom_func = ->
      trans = d3.event.translate
      scale = d3.event.scale
      zoom.attr("transform", "translate(" + trans + ")" + " scale(" + scale + ")")
    @svg.call(@zoom.on("zoom", zoom_func))
    
    zoom.append('rect')
      .attr("id", "bg")
      .attr('width', @width)
      .attr('height', @height)
      .attr('fill', 'white');

  zoom_by: (scale) ->
    x_diff = (scale - 1) * @width
    y_diff = (scale - 1) * @height
    @zoom.scale(@zoom.scale() * scale)
    @zoom.translate([@zoom.translate()[0]-x_diff/2, @zoom.translate()[1]-y_diff/2])
    d3.select("#zoom").attr("transform", "translate(" + @zoom.translate() + ") scale(" + @zoom.scale() + ")")
  
  reset_zoom: ->
    @zoom.scale(1)
    @zoom.translate([0, 0])
    d3.selectAll("#zoom").attr("transform", "translate(0, 0) scale(1)")

  init_callbacks: ->
    t = this

    # so we know where the mouse is on keydown
    $(window).on("mousemove", (e) ->
      t.mouse_x = e.pageX
      t.mouse_y = e.pageY
    )

    $(document).on("keydown", (e) ->
      switch e.keyCode
        # backspace, delete, "D", or "d"
        when 8, 46, 68, 100  
          rebuild = false
          selected = $(".selected").length > 0
          for d in d3.selectAll($(".rel.selected")).data()
            t.remove_rel(d.id)      
            rebuild = true
          for d in d3.selectAll($(".entity.selected")).data()
            t.remove_entity(d.id)
            rebuild = true
          t.build() if rebuild
          e.preventDefault() if selected
        # "A" or "a"
        when 65, 97  
          t.toggle_add_entity_form() if $("#svg:hover").length > 0
    )
    
  toggle_add_entity_form: ->
    form = $("#netmap_add_entity")
    $(@parent_selector).append(form)
    form.css("left", @mouse_x - $(@parent_selector).offset().left - 30 + "px")
    form.css("top", @mouse_y - $(@parent_selector).offset().top - 60 + "px")
    form.css("display", if form.css("display") == "none" then "block" else "none")

  toggle_add_related_entities_form: (entity_id) ->
    entity = @entity_by_id(entity_id)
    form = $("#netmap_add_related_entities")
    $(@parent_selector).append(form)
    $("#netmap_add_related_entities_entity_id").val(entity_id)
    form.css("left", entity.x + @zoom.translate()[0] + 40 + "px")
    form.css("top", entity.y + @zoom.translate()[1] - 30 + "px")
    form.css("display", if form.css("display") == "none" then "block" else "none")
                    
  set_data: (data, center_entity_id = null) ->
    @_original_data = { "entities": data.entities.slice(0), "rels": data.rels.slice(0) }
    @_data = data    
    @set_center_entity_id(center_entity_id) if center_entity_id?
    entity_index = []
    for e, i in @_data.entities
      e.id = Number(e.id)
      e.px = e.x unless e.px?
      e.py = e.y unless e.py?
      entity_index[Number(e.id)] = i
    for r in @_data.rels
      r.id = Number(r.id)
      r.entity1_id = Number(r.entity1_id)
      r.entity2_id = Number(r.entity2_id)
      r.category_id = Number(r.category_id)
      r.category_ids = r.category_ids.split(",") if r.category_ids? and typeof r.category_ids == "string"
      r.category_ids = r.category_ids.map(Number) if r.category_ids instanceof Array
      r.is_current = Number(r.is_current)
      r.source = @_data.entities[entity_index[Number(r.entity1_id)]]
      r.target = @_data.entities[entity_index[Number(r.entity2_id)]]
    
  data: ->
    @_data

  entity_ids: ->
    @_data.entities.map((e) -> Number(e.id))

  entities: ->
    @_data.entities

  rel_ids: ->
    @_data.rels.map((r) -> Number(r.id))

  rels: ->
    @_data.rels

  set_user_id: (user_id) ->
    @user_id = user_id

  set_network_map_id: (id) ->
    @network_map_id = id

  get_network_map_id: ->
    @network_map_id

  save_map: (callback = null) ->
    @remove_hidden_rels()
    if @network_map_id?
      @update_map(callback)
    else
      @create_map(callback)

  api_data_callback: (callback = null, redirect = false) ->
    t = this  
    (data) ->
      t.network_map_id = data.id
      t.set_data(data.data)
      t.build()
      callback.call(t, data.id) if callback?
      window.location.href = "http://littlesis.org/map/" + t.network_map_id if redirect
      
  create_map: (callback = null) ->
    t = this
    @api.create_map(@width, @height, @user_id, @_data, @api_data_callback(callback, true))

  load_map: (id, callback = null) ->
    @network_map_id = id
    t = this
    @api.get_map(id, @api_data_callback(callback))

  reload_map: ->
    if @network_map_id?
      @load_map(@network_map_id) 
    else
      @set_data(@_original_data)
      @build()
      @wheel()
    
  update_map: (callback = null) ->
    return unless @network_map_id?
    t = this
    @api.update_map(@network_map_id, @width, @height, @_data, @api_data_callback(callback))

  data_for_save: ->
    { "width": @width, "height": @height, "user_id": @user_id, "data": JSON.stringify(@_data) }  

  search_entities: (q, callback = null) ->
    @api.search_entities(q, callback)
    
  add_entity: (id, position = null) ->
    return false if @entity_ids().indexOf(parseInt(id)) > -1
    t = this
    @api.get_add_entity_data(id, @entity_ids(), (data) ->
      data.entities = data.entities.map((e) ->
        e.x = if position? then position[0] else t.width/2 + 200 * (0.5 - Math.random())
        e.y = if position? then position[1] else t.height/2 + 200 * (0.5 - Math.random())
        e
      )
      new_data = {
        "entities": t.data().entities.concat(data.entities),
        "rels": t.data().rels.concat(data.rels)
      };
      t.set_data(new_data)
      t.build()
    )

  add_related_entities: (entity_id, num = 10, include_cats = []) ->
    entity = @entity_by_id(entity_id)
    return false unless entity?
    t = this
    @api.get_add_related_entities_data(entity_id, num, @entity_ids(), @rel_ids(), include_cats, (data) ->
      data.entities = t.circle_entities_around_point(data.entities, [entity.x, entity.y])
      t.set_data({
        "entities": t.data().entities.concat(data.entities),
        "rels": t.data().rels.concat(data.rels)
      })
      # t.move_entities_inbounds()
      t.build()
    )
    true

  move_entities_inbounds: ->
    for e in @_data.entities
      e.x = 70 if e.x < 70
      e.x = @width if e.x > @width
      e.y = 50 if e.y < 50
      e.y = @height if e.y > @height

  circle_entities_around_point: (entities, position, radius = 150) ->
    for e, i in entities
      angle = i * ((2 * Math.PI) / entities.length)
      e.x = position[0] + radius * Math.cos(angle)
      e.y = position[1] + radius * Math.sin(angle)
    entities
      
  prune: ->
    @remove_hidden_rels()
    for e in @unconnected_entities()
      @remove_entity(e.id)
    @build()

  show_all_rels: ->
    for rel in @_data.rels
      delete rel["hidden"]
    @build()

  limit_to_cats: (cat_ids) ->
    for rel in @_data.rels
      if rel.category_ids?
        if rel.category_ids.filter((id) -> cat_ids.indexOf(Number(id)) > -1).length > 0
          rel.hidden = false
        else
          rel.hidden = true      
      else
        rel.hidden = cat_ids.indexOf(Number(rel.category_id)) == -1
    @build()

  limit_to_current: ->
    for rel in @_data.rels
      if rel.is_current == 1
        rel.hidden = false
      else
        rel.hidden = true
    @build()

  remove_hidden_rels: ->
    @_data.rels = @_data.rels.filter((r) -> !r.hidden)    
    @build()
    
  unconnected_entities: ->
    connected_ids = []
    for r in @_data.rels
      connected_ids.push(parseInt(r.entity1_id))
      connected_ids.push(parseInt(r.entity2_id))
    @_data.entities.filter((e) ->
      connected_ids.indexOf(parseInt(e.id)) == -1
    )
    
  rel_index: (id) ->
    for r, i in @_data.rels
      return i if parseInt(r.id) == parseInt(id)

  rel_by_id: (id) ->
    for r, i in @_data.rels
      return r if parseInt(r.id) == parseInt(id)

  remove_rel: (id) ->
    @_data.rels.splice(@rel_index(id), 1)

  entity_index: (id) ->
    for e, i in @_data.entities
      return i if parseInt(e.id) == parseInt(id)

  entity_by_id: (id) ->
    for e, i in @_data.entities
      return e if parseInt(e.id) == parseInt(id)
    return null

  remove_entity: (id) ->
    @_data.entities.splice(@entity_index(id), 1)
    @remove_orphaned_rels()
    
  rels_by_entity : (id) ->
    @_data.rels.filter((r) -> 
      parseInt(r.entity1_id) == parseInt(id) || parseInt(r.entity2_id) == parseInt(id)
    )
          
  set_center_entity_id: (id) ->
    @center_entity_id = id
    for entity in @_data["entities"]
      if entity.id == @center_entity_id
        entity.fixed = true
        entity.x = @width / 2
        entity.y = @height / 2

  wheel: (center_entity_id = null) ->
    center_entity_id = @center_entity_id if @center_entity_id?
    return @halfwheel(center_entity_id) if center_entity_id?
    count = 0
    for entity, i in @_data["entities"]
      if parseInt(entity.id) == center_entity_id
        @_data["entities"][i].x = @width/2
        @_data["entities"][i].y = @height/2
      else        
        angle = (2 * Math.PI / (@_data["entities"].length - (if center_entity_id? then 1 else 0))) * count
        @_data["entities"][i].x = @width/2 + @distance * Math.cos(angle)
        @_data["entities"][i].y = @height/2 + @distance * Math.sin(angle)      
        count++
    @update_positions()

  halfwheel: (center_entity_id = null) ->
    center_entity_id = @center_entity_id if @center_entity_id?
    return unless center_entity_id?    
    count = 0
    for entity, i in @_data["entities"]
      if parseInt(entity.id) == center_entity_id
        @_data["entities"][i].x = @width/2
        @_data["entities"][i].y = 30
      else        
        range = Math.PI * 2/3
        angle = Math.PI + (Math.PI / (@_data["entities"].length-2)) * count
        @_data["entities"][i].x = 70 + (@width-140)/2 + ((@width-140)/2) * Math.cos(angle)
        @_data["entities"][i].y = 30 - ((@width-140)/2) * Math.sin(angle)  
        count++
    @update_positions()

  grid: ->
    num = @_data.entities.length
    area = @width * @height
    per = (area / num) * 0.7
    radius = Math.floor(Math.sqrt(per))
    x_num = Math.ceil(@width / (radius * 1.25))
    y_num = Math.ceil(@height / (radius * 1.25))
    for i in [0..x_num-1]
      for j in [0..y_num-1]
        k = x_num * j + i
        if @_data.entities[k]?
          @_data.entities[k].x = i * radius + 70 + (50 - 50 * Math.random())
          @_data.entities[k].y = j * radius + 30 + (50 - 50 * Math.random())
    @update_positions()

  interlocks: (degree0_id, degree1_ids, degree2_ids) ->
    d0 = @entity_by_id(degree0_id)
    d0.x = @width/2
    d0.y = 30    
    for id, i in degree1_ids
      range = Math.PI * 1/2
      angle = (Math.PI * 3/2) + i * (range / (degree1_ids.length-1)) - range/2
      radius = (@width-100)/2
      d1 = @entity_by_id(id)
      d1.x = 70 + i * (@width-140)/(degree1_ids.length-1)
      d1.y = @height/2 + 250 + (radius) * Math.sin(angle)
    for id, i in degree2_ids
      range = Math.PI * 1/3
      angle = (Math.PI * 3/2) + i * (range / (degree2_ids.length-1)) - range/2
      radius = (@width-100)/2
      d2 = @entity_by_id(id)
      d2.x = 70 + i * (@width-140)/(degree2_ids.length-1)
      d2.y = @height - 480 - radius * Math.sin(angle)
    @update_positions()    
    
  shuffle_array: (array) ->
    counter = array.length
    # While there are elements in the array
    while counter--
      # Pick a random index
      index = (Math.random() * counter) | 0

      # And swap the last element with it
      temp = array[counter]
      array[counter] = array[index]
      array[index] = temp
    array

  shuffle: ->
    positions = @entities().map((e) -> return [e.x, e.y])
    positions = @shuffle_array(positions)
    for p, i in positions
      @entities()[i].x = p[0]
      @entities()[i].y = p[1]
    @update_positions()

  has_positions: ->
    for e in @_data.entities
      return false unless e.x? and e.y?
    for r in @_data.rels
      return false unless r.source.x? and r.source.y? and r.target.x? and r.target.y?
    true

  update_positions: ->
    d3.selectAll(".entity").attr("transform", (d) -> "translate(" + d.x + "," + d.y + ")")
    d3.selectAll(".rel").attr("transform", (d) -> "translate(" + (d.source.x + d.target.x)/2 + "," + (d.source.y + d.target.y)/2 + ")")
    d3.selectAll(".line")
      .attr("x1", (d) -> d.source.x - (d.source.x + d.target.x)/2)
      .attr("y1", (d) -> d.source.y - (d.source.y + d.target.y)/2)
      .attr("x2", (d) -> d.target.x - (d.source.x + d.target.x)/2)
      .attr("y2", (d) -> d.target.y - (d.source.y + d.target.y)/2)  
    d3.selectAll(".rel text")
      .attr("transform", (d) ->
        x_delta = d.target.x - d.source.x
        y_delta = d.target.y - d.source.y
        angle = Math.atan2(y_delta, x_delta) * 180 / Math.PI
        angle += 180 if d.source.x >= d.target.x
        "rotate(" + angle + ")"
      )    
                
  use_force: ->
    for e, i in @_data.entities
      delete @_data.entities[i]["fixed"]
    @force_enabled = true
    @force = d3.layout.force()
      .gravity(.3)
      .distance(@distance)
      .charge(-5000)
      .friction(0.7)
      .size([@width, @height])
      .nodes(@_data.entities, (d) -> return d.id)
      .links(@_data.rels, (d) -> return d.id)
      .start()
    t = this
    @force.on("tick", () ->
      t.update_positions()
    )
    @force.alpha(@alpha) if @alpha? && @alpha > 0

  one_time_force: ->
    @deny_force() if @force_enabled
    @use_force()
    @force.alpha(0.3)
    t = this
    @force.on("end", -> t.force_enabled = false)

  deny_force: ->
    @force_enabled = false
    @alpha = @force.alpha()
    @force.stop()

  n_force_ticks: (n) ->
    @use_force()
    for [1..n]
      @force.tick()
    @deny_force()

  build: ->
    @build_rels()
    @build_entities()
    @entities_on_top()
    @update_positions() if @has_positions()

  remove_orphaned_rels: ->
    entity_ids = @_data.entities.map((e) -> e.id)
    rel_ids = (rel.id for rel, i in @_data.rels when entity_ids.indexOf(rel.entity1_id) == -1 or entity_ids.indexOf(rel.entity2_id) == -1)
    for id in rel_ids
      @remove_rel(id)
        
  build_rels: ->
    t = this
    zoom = d3.select("#zoom")

    # rels are made of groups of parts...
    rels = zoom.selectAll(".rel")
      .data(@_data["rels"], (d) -> return d.id)

    groups = rels.enter().append("g")
      .attr("class", "rel")
      .attr("id", (d) -> d.id)

    # begin with lines
    groups.append("line")
      .attr("class", "line")
      .attr("opacity", 0.6)
      .style("stroke-width", (d) ->
        Math.sqrt(d.value) * 12;
      )

    # anchor tags around category labels
    groups.append("a")
      .attr("xrel:href", (d) -> d.url)
      .append("text")
      .attr("dy", (d) -> Math.sqrt(d.value) * 10 / 2 - 1)
      .attr("text-anchor", "middle") 
      .text((d) -> d.label)

    rels.exit().remove()

    # hide hidden rels
    rels.style("display", (d) -> if d.hidden == true then "none" else null)

    # ensure lines and anchors and text also receive new data
    @svg.selectAll(".rel .line")
      .data(@_data["rels"], (d) -> return d.id)
    @svg.selectAll(".rel a")
      .data(@_data["rels"], (d) -> return d.id)
      .on("click", (d) ->
        window.location.href = d.url
      )
    @svg.selectAll(".rel text")
      .data(@_data["rels"], (d) -> return d.id)
    
    @svg.selectAll(".rel").on("click", (d, i) ->
      t.toggle_selected_rel(d.id)
    )

  toggle_selected_rel: (id, value = null) ->
    rel = $("#" + id + ".rel")
    if value?
      klass = if value then "rel selected" else "rel"
    else
      klass = if rel.attr("class") == "rel" then "rel selected" else "rel"    
    rel.attr("class", klass)

  build_entities: ->
    t = this
    zoom = d3.selectAll("#zoom")

    entity_drag = d3.behavior.drag()
      .on("dragstart", (d, i) ->
        t.alpha = t.force.alpha() if t.force_enabled
        t.force.stop() if t.force_enabled
        t.drag = false
        d3.event.sourceEvent.preventDefault()
        d3.event.sourceEvent.stopPropagation()
      )
      .on("drag", (d, i) ->
        d.px += d3.event.dx
        d.py += d3.event.dy
        d.x += d3.event.dx
        d.y += d3.event.dy

        t.update_positions()
        t.drag = true
      )
      .on("dragend", (d, i) -> 
        d.fixed = true
        t.force.alpha(t.alpha) if t.force_enabled
      )

    # entities are made of groups of parts...
    entities = zoom.selectAll(".entity")
      .data(@_data["entities"], (d) -> return d.id)
    
    groups = entities.enter().append("g")
      .attr("class", "entity")
      .attr("id", (d) -> d.id)
      .call(entity_drag)

    has_image = (d) -> 
      d.image.indexOf("anon") == -1

    # a solid rectangle behind the entity's image
    groups.append("rect")
      .attr("class", "image_rect")
      .attr("fill", @entity_background_color)
      .attr("opacity", 1)
      # .attr("rx", @entity_background_corner_radius)
      # .attr("ry", @entity_background_corner_radius)
      .attr("width", 58) # (d) -> if has_image(d) then 58 else 43)
      .attr("height", 58) # (d) -> if has_image(d) then 58 else 43)
      .attr("x", -29) # (d) -> if has_image(d) then -29 else -21)
      .attr("y", -29) # (d) -> if has_image(d) then -29 else -29)
      .attr("stroke", "#f8f8f8")
      .attr("stroke-width", 1)

    # profile image or default silhouette
    groups.append("image")
      .attr("class", "image")
      # because the default pics are too dark:
      .attr("opacity", (d) -> if has_image(d) then 1 else 0.5) 
      .attr("xlink:href", (d) -> d.image)
      .attr("x", -25) # (d) -> if has_image(d) then -25 else -17)
      .attr("y", -25) # (d) -> if has_image(d) then -25 else -25)
      .attr("width", 50) # (d) -> if has_image(d) then 50 else 35)
      .attr("height", 50) # (d) -> if has_image(d) then 50 else 35)

    # add related entities button and background squares
    buttons = groups.append("a")
      .attr("class", "add_button")
    buttons.append("text")
      .attr("dx", 30)
      .attr("dy", -20)
      .text("+")
      .on("click", (d) ->
        t.toggle_add_related_entities_form(d.id)
      )      
    groups.insert("rect", ":first-child")
      .attr("class", "add_button_rect")
      .attr("x", 29)
      .attr("y", -29)
      .attr("fill", @entity_background_color)
      .attr("opacity", @entity_background_opacity)
      .attr("width", 10)
      .attr("height", 10)
          
    # anchor tags around entity name
    links = groups.append("a")
      .attr("class", "entity_link")
      .attr("xlink:href", (d) -> d.url)
      .attr("title", (d) -> d.description)
    
    links.append("text")
      .attr("dx", 0)
      .attr("dy", 40) # (d) -> if has_image(d) then 40 else 25)
      .attr("text-anchor","middle")
      .text((d) -> t.split_name(d.name)[0])

    links.append("text")
      .attr("dx", 0)
      .attr("dy", 55) # (d) -> if has_image(d) then 55 else 40)
      .attr("text-anchor","middle")
      .text((d) -> t.split_name(d.name)[1])

    # one or two rectangles behind the entity name
    groups.filter((d) -> t.split_name(d.name)[0] != d.name)
      .insert("rect", ":first-child")
      .attr("fill", @entity_background_color)
      .attr("opacity", @entity_background_opacity)
      .attr("rx", @entity_background_corner_radius)
      .attr("ry", @entity_background_corner_radius)
      .attr("x", (d) -> 
        -$(this.parentNode).find(".entity_link text:nth-child(2)").width()/2 - 3
      )
      .attr("y", (d) ->
        image_offset = $(this.parentNode).find("image").attr("height")/2
        text_offset = $(this.parentNode).find(".entity_link text").height()
        extra_offset = 2 # if has_image(d) then 2 else -5
        image_offset + text_offset + extra_offset
      )
      .attr("width", (d) -> $(this.parentNode).find(".entity_link text:nth-child(2)").width() + 6)
      .attr("height", (d) -> $(this.parentNode).find(".entity_link text:nth-child(2)").height() + 4)

    groups.insert("rect", ":first-child")
      .attr("fill", @entity_background_color)
      .attr("opacity", @entity_background_opacity)
      .attr("rx", @entity_background_corner_radius)
      .attr("ry", @entity_background_corner_radius)
      .attr("x", (d) ->
        -$(this.parentNode).find(".entity_link text").width()/2 - 3
      )
      .attr("y", (d) ->
        image_offset = $(this.parentNode).find("image").attr("height")/2
        extra_offset = 1 # if has_image(d) then 1 else -6
        image_offset + extra_offset
      )
      .attr("width", (d) -> $(this.parentNode).find(".entity_link text").width() + 6)
      .attr("height", (d) -> $(this.parentNode).find(".entity_link text").height() + 4)

    entities.exit().remove()

    @svg.selectAll(".entity").on("click", (d, i) ->
      t.toggle_selected_entity(d.id) unless t.drag
    )

    @svg.selectAll(".entity a").on("click", (d, i) ->
      d3.event.stopPropagation()
    )
    
  toggle_selected_entity: (id) ->
    g = $("#" + id + ".entity")
    klass = if g.attr("class") == "entity" then "entity selected" else "entity"
    g.attr("class", klass)
    
    # toggle selection for entity's relationships (so that they're selected
    selected = g.attr("class") == "entity selected"
    for r in @rels_by_entity(id)
      @toggle_selected_rel(r.id, selected)
  
  entities_on_top: ->
    zoom = $("#zoom")
    $("g.rel").each((i, g) -> $(g).prependTo(zoom))
    $("#bg").prependTo(zoom);

  split_name: (name, min_length = 16) ->
    return ["", ""] unless name?

    name = name.trim()

    # return whole name if name too short
    return [name, ""] if name.length < min_length

    # look for space between 1/2 - 2/3 of string
    i = name.indexOf(" ", Math.floor(name.length * 1/2))
    if i > -1 && i <= Math.floor(name.length * 2/3)
      return [name.substring(0, i), name.substring(i+1)]
    else
      # look for space between 1/3 - 1/2 of string
      i = name.lastIndexOf(" ", Math.ceil(name.length/2))
      if i >= Math.floor(name.lenth * 1/3)
        return [name.substring(0, i), name.substring(i+1)]                

    # split on the middle space
    parts = name.split(/\s+/)
    half = Math.ceil(parts.length / 2)
    [parts.slice(0, half).join(" "), parts.slice(half).join(" ")]
      
if typeof module != "undefined" && module.exports
  # on a server
  exports.Netmap = Netmap
else
  # on a client
  window.Netmap = Netmap
