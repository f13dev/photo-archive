class Family_Tree {

    constructor(data) {
        this.data = data;
        this.zoom = 1.0;
    }

    show(pid) {
        // Find connections for member
        let tree = this;
        this.setUserData(pid);

        let person = this.person;

        let content = '';
        if (person.parents.length) {
            content += '<ul>';
                content += '<li>';
                    content += '<div>';
                        person.parents.forEach(function(p) {
                            content += '<span data-id="'+p.id+'" class="tree_person '+p.gender+'">'+p.name+'</span> ';
                        });  
                    content += '</div>';
        }   

                    content += '<ul>';
                        content += '<li>';
                            content += '<div>';
                                content += '<span data-id="'+person.id+'" class="tree_person '+person.gender+'">'+person.name+'</span> ';
                                if (person.partners.length) {
                                    person.partners.forEach(function(p) {
                                        content += '<span data-id="'+p.id+'" class="tree_person '+p.gender+'">'+p.name+'</span> ';
                                    });
                                } 
                            content += '</div>';
                            
                            if (person.children.length) {
                                content += '<ul>';
       
                                    person.children.forEach(function(p) {
                                        content += '<li>';
                                            content += '<div>';
                                                content += '<span data-id="'+p.id+'" class="tree_person '+p.gender+'">'+p.name+'</span> ';
                                            content += '</div>';
                                        content += '</li>';
                                    });
                                 
                                content += '</ul>';
                            }
                        content += '</li>';
                    content += '</ul>';

        if (person.parents.length) {
                content += '</li>';
            content += '</ul>';
        }
        
        $('#tree').html(content);
    
        let people = document.querySelectorAll('.tree_person');

        people.forEach(function(p) {
            p.addEventListener('click', function(peep) {
                tree.show(this.dataset.id);
            });
        });


    }

    recursiveChildren(data) {
        let tree = this;
        let c = '';
        if (data.children.length) {
            c += '<ul>';
                data.children.forEach(function(p) {
                    c += '<li class="tree-group">';
                        c += '<div>';
                            c += tree.recursivePerson(p.id);
                            if (tree.data[p.id].pids.length) {
                                tree.data[p.id].pids.forEach(function(part) {
                                    c += tree.recursivePerson(part);
                                })
                            }
                            c += '</div>';
                        c += tree.recursiveChildren(p);
                    c += '</li>';
                });
            c += '</ul>';
        }
        return c;
    }

    recursivePerson(pid) {
        let p = this.data[pid];
        console.log(p);
        let c = ' <span data-id="'+p.id+'" class="tree_person '+p.gender+'">';
            c += p.name;
            //if (p.dob != '' || p.dod != '') {
                c += '<br>';
                c += 'DoB: '+p.dob+' | ';
                c += 'DoD: '+p.dod;
                /*if (p.dob != '') {
                    c += 'DoB: '+p.dob;
                    if (p.dod != '') {
                        c += ' | ';
                    }
                }
                if (p.dod != '') {
                    c += 'DoD: '+p.dod;
                } else {
                    c += 'DoD: ';
                }*/
            //}
            if (p.image != "") {
                c += '<br>';
                c += '<img src="'+p.image+'">';
            }
            if (p.search_url != "") {
                c += '<br>';
                c += '<a href="'+p.search_url+'">View photos</a>';
            }
        c += '</span> ';

        return c;
    }

    recursiveParents(treeData, c) {
        let tree = this;
        if (treeData.parents.length) {
            let a = '<ul>';
                a += '<li class="tree-group">';
                    a += '<div>';
                        treeData.parents.forEach(function(p) {
                            a += tree.recursivePerson(p.id);
                        });
                    a += '</div>';
                    a += c;
                a += '</li>';
            a += '</ul>';

            return a;
        }

        return c;        
    }

    recursiveShow(pid) {
        let tree = this;
        let treeData = tree.factorData(pid);
        console.log(treeData);

        let c = '<ul>';
            c += '<li class="tree-group">';
                c += '<div>';
                    c += tree.recursivePerson(pid);
                    if (treeData.partners !== undefined) {
                        treeData.partners.forEach(function(p) {
                            c += tree.recursivePerson(p.id);
                        });
                    }
                c += '</div>';
                c += tree.recursiveChildren(treeData);
            c += '</li>';
        c += '</ul>';

        c = tree.recursiveParents(treeData, c);

        $('#tree_recursive').html(c);

        let people = document.querySelectorAll('.tree_person');

        people.forEach(function(p) {
            p.addEventListener('click', function(peep) {
                tree.recursiveShow(this.dataset.id);
            });
        });

        console.log('here');

        let zoomout = document.querySelector('#zoomout');
        zoomout.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('zoomout');
            tree.zoom = tree.zoom - 0.1;
            $('#tree_recursive').css('transform', 'scale('+tree.zoom+')');
        });

        let zoomin = document.querySelector('#zoomin');
        zoomin.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('zoomin');
            tree.zoom = tree.zoom + 0.1;
            console.log(tree.zoom);
            $('#tree_recursive').css('transform', 'scale('+tree.zoom+')');
        });
    }

    recursiveParentArray(pid) {
        const tree = this;
        let parents = [];
        if (tree.data[pid] !== undefined && Number(tree.data[pid].mother) > 0) {
            parents[tree.data[pid].mother] = tree.data[tree.data[pid].mother];
            parents[tree.data[pid].mother].parents = tree.recursiveParentArray(tree.data[pid].mother);
        }
        if (tree.data[pid] !== undefined && Number(tree.data[pid].father) > 0) {
            parents[tree.data[pid].father] = tree.data[tree.data[pid].father];
            parents[tree.data[pid].father].parents = tree.recursiveParentArray(tree.data[pid].father);
        }
        return parents;
    }

    recursiveChildrenArray(pid) {
        const tree = this;
        let children = [];
        tree.data.forEach(function(p) {
            if (p.father == pid || p.mother == pid) {
                children[p.id] = p;
                children[p.id].children = tree.recursiveChildrenArray(p.id);
            } 
        });
        return children;
    }

    recursiveSiblingsArray(pid) {
        const tree = this;
        let mother = tree.data[pid].mother;
        let father = tree.data[pid].father;

        let siblings = [];
        tree.data.forEach(function(p) {
            if ((p.father == father || p.mother == mother) && p.id != pid) {
                siblings[p.id] = p;
            }
        });

        return siblings;
    }

    recursivePartnersArray(pid) {
        const tree = this;
        let partners = [];
        if (tree.data[pid].pids.length) {
            tree.data[pid].pids.forEach(function(p) {
                partners[p] = tree.data[p];
            });
        } 

        return partners;
    }

    factorData(pid) {
        const tree = this;
        let tempData = tree.data[pid];
        // Recursively add parents
        tempData.parents = tree.recursiveParentArray(pid);
        // Recursively add children
        tempData.children = tree.recursiveChildrenArray(pid);
        // Add partners
        tempData.partners = tree.recursivePartnersArray(pid);
        // Add siblings
        tempData.siblings = tree.recursiveSiblingsArray(pid);

        return tempData;
    }

    setUserData(pid) {
        const tree = this;
        tree.person = tree.data[pid];
        let partners = [];
        let parents = [];
        let children = [];
        tree.person.pids.forEach(function(a, b) {
            partners[a] = tree.data[a];
        });
        if (tree.person.mother) {
            parents[tree.person.mother] = tree.data[tree.person.mother];
        }
        if (tree.person.father) {
            parents[tree.person.father] = tree.data[tree.person.father];
        }
        tree.data.forEach(function(d) {
            if (d.father == tree.person.id || d.mother == tree.person.id) {
                children[d.id] = d;
            }
        });
        tree.person.partners = partners;
        tree.person.parents = parents;
        tree.person.children = children;
    }

    handleClicks() {
        
    }
}
