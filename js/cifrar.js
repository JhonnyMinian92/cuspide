async function cifrar(textoPlano, llavePublicaPem) {
  const pemBody = llavePublicaPem.replace('-----BEGIN PUBLIC KEY-----', '').replace('-----END PUBLIC KEY-----', '').replace(/\s+/g, '');

  const binaryDer = Uint8Array.from(atob(pemBody), (c) => c.charCodeAt(0));

  const cryptoKey = await window.crypto.subtle.importKey('spki', binaryDer.buffer, { name: 'RSA-OAEP', hash: 'SHA-256' }, false, ['encrypt']);

  const encryptedBuffer = await window.crypto.subtle.encrypt({ name: 'RSA-OAEP' }, cryptoKey, new TextEncoder().encode(textoPlano));

  const encryptedBytes = new Uint8Array(encryptedBuffer);
  return btoa(String.fromCharCode(...encryptedBytes));
}
