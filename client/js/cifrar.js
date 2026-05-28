const CUSPIDE_PUBLIC_KEY = `-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAw9HrJZThGSSmncoFTjMq
CWlVcUMUaVeXzz/Hhjd8qcxiXv6CnzXbEyEyE8X4eQb91mn/bIUP9vWai3mdP/H4
VeHXoGWpdt1ulXKue4kwrdwODc4O4mlU4pdJPEBTq5AL4FZp79Bb+icNp8l1AFAP
ORUaL9ivEMIxS/5ntMoN0YV8aFWxB+4ulmgEqFE3MLdcoz0vFHZiJnperJY2DERv
tJVR/47+v9ZZyjLBzjwj2CLSbrfMRsCErytRCOlcKOQujPXeSVnZh38jC2Mwujky
pxvp9HwCRbR5dqOsocxHRucz5UJPSI9rqI3o7iYYsHvkbL+QPPdlOJkRx9g7v/Ns
8wIDAQAB
-----END PUBLIC KEY-----`;

async function cifrar(textoPlano, llavePublicaPem = CUSPIDE_PUBLIC_KEY) {
  // crypto.subtle solo funciona en HTTPS; usar forge como fallback en HTTP
  if (window.crypto && window.crypto.subtle) {
    const pemBody = llavePublicaPem
      .replace('-----BEGIN PUBLIC KEY-----', '')
      .replace('-----END PUBLIC KEY-----', '')
      .replace(/\s+/g, '');

    const binaryDer = Uint8Array.from(atob(pemBody), (c) => c.charCodeAt(0));
    const cryptoKey = await window.crypto.subtle.importKey(
      'spki',
      binaryDer.buffer,
      { name: 'RSA-OAEP', hash: 'SHA-1' },
      false,
      ['encrypt']
    );

    const encryptedBuffer = await window.crypto.subtle.encrypt(
      { name: 'RSA-OAEP' },
      cryptoKey,
      new TextEncoder().encode(textoPlano)
    );

    return btoa(String.fromCharCode(...new Uint8Array(encryptedBuffer)));
  }

  // Fallback: node-forge (disponible en HTTP)
  if (typeof forge === 'undefined') {
    throw new Error('La página requiere HTTPS o la librería forge no está cargada.');
  }

  const publicKey = forge.pki.publicKeyFromPem(llavePublicaPem);
  const encrypted = publicKey.encrypt(textoPlano, 'RSA-OAEP', {
    md: forge.md.sha1.create(),
    mgf1: { md: forge.md.sha1.create() }
  });
  return forge.util.encode64(encrypted);
}

async function cifrarLogin(username, password, llavePublicaPem = CUSPIDE_PUBLIC_KEY) {
  const payload = JSON.stringify({ username, password });
  return cifrar(payload, llavePublicaPem);
}
