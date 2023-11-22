const data = {
    'products': [
        {
            'id': 1,
            'name': 'T-shirt',
            'price': 10,
            'quantity': 1,
            'image': 'https://picsum.photos/200/300?random=1'
        },
        {
            'id': 2,
            'name': 'Pantalon',
            'price': 20,
            'quantity': 2,
            'image': 'https://picsum.photos/200/300?random=2'
        },
        {
            'id': 3,
            'name': 'Chaussures',
            'price': 30,
            'quantity': 1,
            'image': 'https://picsum.photos/200/300?random=3'
        }
    ]
}

document.addEventListener('DOMContentLoaded', () => {

    localStorage.setItem('panier', JSON.stringify(data.products))
    datas = JSON.parse(localStorage.getItem('panier'))

    afficheArticle(datas)

    document.getElementById('ajouter').addEventListener('click', (e) => {
        e.preventDefault();
        const nom = document.getElementById('nom').value;
        const prix = document.getElementById('prix').value;
        const newProduct = {
            'id': datas.length + 1,
            'name': nom,
            'price': prix,
            'quantity': 1,
            'image': 'https://picsum.photos/200/300?random=' + (datas.length + 1)
        }
        datas.push(newProduct)
        localStorage.setItem('panier', JSON.stringify(datas))
        afficheArticle(datas)
    })

})

function afficheArticle(datas) {
    const panier = document.getElementById('panier')
    panier.innerHTML = ''
    datas.forEach(product => {
        const article = document.createElement('article')
        article.classList.add('product')
        article.innerHTML = `
      <img src="${product.image}" alt="${product.name}">
      <h3>${product.name}</h3>
      <p>${product.price} €</p>
      <button class="btn btn-primary">Ajouter au panier</button>
    `
        panier.appendChild(article)
    })
}